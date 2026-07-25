"""
PROJECT 14: ROCKET SOFT LANDING (1D) - 2nd Order ODE with Thrust Limits and Event Detection

OBJECTIVE:
Land a rocket vertically from initial altitude z0 with bounded thrust control.
Compare bang-bang control vs PID control on velocity to achieve soft landing.

PHYSICS:
- 2nd order ODE: z''(t) = -g + T(t)/m (thrust T counteracts gravity g)
- State variables: z (altitude), v (velocity)
- Constraints: 0 ≤ T ≤ T_max (thrust limits)
- Goal: Land at z=0 with v≈0 (soft landing)

CONTROL STRATEGIES:
1. Bang-Bang: Full thrust ON/OFF based on threshold
2. PID: Proportional-Integral-Derivative control on velocity error

AUTHOR: Career Hub Project
DATE: December 4, 2025
"""

import numpy as np
import matplotlib.pyplot as plt
from scipy.integrate import solve_ivp
from dataclasses import dataclass
import warnings
warnings.filterwarnings('ignore')


@dataclass
class RocketParameters:
    """Physical parameters of the rocket system"""
    m: float = 1000.0           # Mass (kg)
    g: float = 9.81             # Gravity (m/s²)
    T_max: float = 15000.0      # Maximum thrust (N)
    z0: float = 1000.0          # Initial altitude (m)
    v0: float = -50.0           # Initial velocity (m/s, negative = falling)
    target_velocity: float = -2.0  # Target landing velocity (m/s)
    
    def __post_init__(self):
        """Validate parameters"""
        assert self.m > 0, "Mass must be positive"
        assert self.T_max > 0, "Max thrust must be positive"
        assert self.z0 > 0, "Initial altitude must be positive"
        assert self.target_velocity < 0, "Landing velocity should be negative (downward)"


class BangBangController:
    """
    Bang-Bang Controller: Full thrust ON/OFF strategy
    
    Logic:
    - If velocity is too fast (v < v_threshold), apply FULL thrust
    - Otherwise, apply NO thrust (free fall)
    
    Simple but causes oscillations and fuel waste
    """
    
    def __init__(self, params: RocketParameters, v_threshold: float = -10.0):
        """
        Initialize bang-bang controller
        
        Args:
            params: Rocket parameters
            v_threshold: Velocity threshold to trigger full thrust (m/s)
        """
        self.params = params
        self.v_threshold = v_threshold
        self.thrust_history = []
        self.time_history = []
    
    def compute_thrust(self, t: float, z: float, v: float) -> float:
        """
        Compute thrust based on bang-bang logic
        
        Args:
            t: Current time (s)
            z: Current altitude (m)
            v: Current velocity (m/s)
            
        Returns:
            Thrust force (N)
        """
        # If falling too fast, apply full thrust
        if v < self.v_threshold:
            thrust = self.params.T_max
        else:
            thrust = 0.0
        
        # Record for plotting
        self.thrust_history.append(thrust)
        self.time_history.append(t)
        
        return thrust
    
    def get_name(self) -> str:
        return f"Bang-Bang (threshold={self.v_threshold} m/s)"


class PIDController:
    """
    PID Controller: Smooth thrust control based on velocity error
    
    Control law: T = Kp*e + Ki*∫e + Kd*de/dt
    where e = v_target - v_actual (error in velocity)
    
    Advantages:
    - Smooth control (less oscillation)
    - Better fuel efficiency
    - Adjustable response via tuning Kp, Ki, Kd
    """
    
    def __init__(self, params: RocketParameters, Kp: float = 500.0, 
                 Ki: float = 50.0, Kd: float = 1000.0):
        """
        Initialize PID controller
        
        Args:
            params: Rocket parameters
            Kp: Proportional gain
            Ki: Integral gain
            Kd: Derivative gain
        """
        self.params = params
        self.Kp = Kp
        self.Ki = Ki
        self.Kd = Kd
        
        # PID state
        self.integral_error = 0.0
        self.prev_error = 0.0
        self.prev_time = 0.0
        
        # History for plotting
        self.thrust_history = []
        self.time_history = []
        self.error_history = []
    
    def compute_thrust(self, t: float, z: float, v: float) -> float:
        """
        Compute thrust using PID control law
        
        Args:
            t: Current time (s)
            z: Current altitude (m)
            v: Current velocity (m/s)
            
        Returns:
            Thrust force (N), bounded by [0, T_max]
        """
        # Calculate time step
        dt = t - self.prev_time if t > self.prev_time else 0.01
        
        # Target velocity varies with altitude (slow down as approaching ground)
        # Proportional to remaining altitude for smooth landing
        if z > 100:
            v_target = self.params.target_velocity * (z / self.params.z0) * 10
        else:
            v_target = self.params.target_velocity * np.sqrt(z / 100)
        
        # Velocity error (negative error = too fast)
        error = v_target - v
        
        # PID terms
        P = self.Kp * error
        self.integral_error += error * dt
        I = self.Ki * self.integral_error
        D = self.Kd * (error - self.prev_error) / dt if dt > 0 else 0
        
        # Total thrust (add baseline to counteract gravity)
        baseline_thrust = self.params.m * self.params.g
        thrust = baseline_thrust + P + I + D
        
        # Apply thrust limits [0, T_max]
        thrust = np.clip(thrust, 0, self.params.T_max)
        
        # Update state
        self.prev_error = error
        self.prev_time = t
        
        # Record for analysis
        self.thrust_history.append(thrust)
        self.time_history.append(t)
        self.error_history.append(error)
        
        return thrust
    
    def get_name(self) -> str:
        return f"PID (Kp={self.Kp}, Ki={self.Ki}, Kd={self.Kd})"


class RocketLandingSimulator:
    """
    Simulates rocket landing with different control strategies
    """
    
    def __init__(self, params: RocketParameters, controller):
        """
        Initialize simulator
        
        Args:
            params: Rocket parameters
            controller: Control strategy (BangBangController or PIDController)
        """
        self.params = params
        self.controller = controller
        self.solution = None
    
    def dynamics(self, t: float, state: np.ndarray) -> np.ndarray:
        """
        Rocket dynamics: 2nd order ODE converted to system of 1st order ODEs
        
        State vector: [z, v]
        where z = altitude (m), v = velocity (m/s)
        
        Equations:
            dz/dt = v
            dv/dt = -g + T(t)/m
        
        Args:
            t: Time (s)
            state: [z, v]
            
        Returns:
            [dz/dt, dv/dt]
        """
        z, v = state
        
        # Get thrust from controller
        thrust = self.controller.compute_thrust(t, z, v)
        
        # Acceleration = -gravity + thrust_per_unit_mass
        a = -self.params.g + thrust / self.params.m
        
        return [v, a]
    
    def landing_event(self, t: float, state: np.ndarray) -> float:
        """
        Event function: Detects when rocket reaches ground (z=0)
        
        Returns:
            z (altitude) - solver stops when this crosses zero
        """
        return state[0]  # z = 0 triggers event
    
    # Make event terminal (stop integration when triggered)
    landing_event.terminal = True
    landing_event.direction = -1  # Only trigger when z is decreasing
    
    def simulate(self, t_max: float = 100.0, dt: float = 0.1) -> dict:
        """
        Run simulation until landing or timeout
        
        Args:
            t_max: Maximum simulation time (s)
            dt: Output time step (s)
            
        Returns:
            Dictionary with simulation results
        """
        # Initial state: [z0, v0]
        y0 = [self.params.z0, self.params.v0]
        
        # Time span
        t_span = (0, t_max)
        t_eval = np.arange(0, t_max, dt)
        
        print(f"\n{'='*60}")
        print(f"SIMULATING: {self.controller.get_name()}")
        print(f"{'='*60}")
        print(f"Initial altitude: {self.params.z0:.1f} m")
        print(f"Initial velocity: {self.params.v0:.1f} m/s")
        print(f"Max thrust: {self.params.T_max:.1f} N")
        print(f"Mass: {self.params.m:.1f} kg")
        
        # Solve ODE with event detection
        self.solution = solve_ivp(
            fun=self.dynamics,
            t_span=t_span,
            y0=y0,
            method='RK45',  # Runge-Kutta 4th/5th order
            t_eval=t_eval,
            events=self.landing_event,
            dense_output=True,
            max_step=dt
        )
        
        # Extract results
        t = self.solution.t
        z = self.solution.y[0]
        v = self.solution.y[1]
        
        # Landing metrics
        landing_time = t[-1]
        landing_velocity = v[-1]
        landing_altitude = z[-1]
        
        # Calculate fuel used (integral of thrust over time)
        thrust_array = np.array(self.controller.thrust_history)
        time_array = np.array(self.controller.time_history)
        if len(thrust_array) > 1:
            fuel_used = np.trapz(thrust_array, time_array)
        else:
            fuel_used = 0
        
        # Success criteria
        success = (abs(landing_altitude) < 1.0 and  # Within 1m of ground
                   abs(landing_velocity) < 5.0)      # Landing speed < 5 m/s
        
        print(f"\n{'='*60}")
        print(f"LANDING RESULTS:")
        print(f"{'='*60}")
        print(f"Landing time: {landing_time:.2f} s")
        print(f"Landing velocity: {landing_velocity:.2f} m/s")
        print(f"Landing altitude: {landing_altitude:.2f} m")
        print(f"Fuel used (N·s): {fuel_used:.2f}")
        print(f"Success: {'✓ YES' if success else '✗ NO'}")
        
        if success:
            if abs(landing_velocity) < 2:
                print("Rating: ⭐⭐⭐ EXCELLENT (Soft landing)")
            elif abs(landing_velocity) < 4:
                print("Rating: ⭐⭐ GOOD (Acceptable landing)")
            else:
                print("Rating: ⭐ OK (Hard landing)")
        else:
            print("Rating: ✗ FAILED (Crash or overshoot)")
        
        return {
            'time': t,
            'altitude': z,
            'velocity': v,
            'thrust': np.array(self.controller.thrust_history),
            'thrust_time': np.array(self.controller.time_history),
            'landing_time': landing_time,
            'landing_velocity': landing_velocity,
            'landing_altitude': landing_altitude,
            'fuel_used': fuel_used,
            'success': success,
            'controller_name': self.controller.get_name()
        }


def plot_comparison(results_list: list):
    """
    Create comprehensive comparison plots for multiple control strategies
    
    Args:
        results_list: List of result dictionaries from simulate()
    """
    fig, axes = plt.subplots(2, 2, figsize=(15, 10))
    fig.suptitle('Rocket Soft Landing: Control Strategy Comparison', 
                 fontsize=16, fontweight='bold')
    
    colors = ['blue', 'red', 'green', 'orange', 'purple']
    
    # Plot 1: Altitude vs Time
    ax1 = axes[0, 0]
    for i, result in enumerate(results_list):
        ax1.plot(result['time'], result['altitude'], 
                label=result['controller_name'], 
                color=colors[i % len(colors)], linewidth=2)
    ax1.set_xlabel('Time (s)', fontsize=11)
    ax1.set_ylabel('Altitude (m)', fontsize=11)
    ax1.set_title('Altitude Profile', fontsize=12, fontweight='bold')
    ax1.grid(True, alpha=0.3)
    ax1.legend()
    ax1.axhline(y=0, color='black', linestyle='--', linewidth=1, alpha=0.5)
    
    # Plot 2: Velocity vs Time
    ax2 = axes[0, 1]
    for i, result in enumerate(results_list):
        ax2.plot(result['time'], result['velocity'], 
                label=result['controller_name'], 
                color=colors[i % len(colors)], linewidth=2)
    ax2.set_xlabel('Time (s)', fontsize=11)
    ax2.set_ylabel('Velocity (m/s)', fontsize=11)
    ax2.set_title('Velocity Profile', fontsize=12, fontweight='bold')
    ax2.grid(True, alpha=0.3)
    ax2.legend()
    ax2.axhline(y=0, color='black', linestyle='--', linewidth=1, alpha=0.5)
    ax2.axhline(y=-2, color='green', linestyle=':', linewidth=1, alpha=0.5, 
                label='Target velocity')
    
    # Plot 3: Thrust vs Time
    ax3 = axes[1, 0]
    for i, result in enumerate(results_list):
        if len(result['thrust']) > 0:
            ax3.plot(result['thrust_time'], result['thrust'] / 1000, 
                    label=result['controller_name'], 
                    color=colors[i % len(colors)], linewidth=2)
    ax3.set_xlabel('Time (s)', fontsize=11)
    ax3.set_ylabel('Thrust (kN)', fontsize=11)
    ax3.set_title('Thrust Control Signal', fontsize=12, fontweight='bold')
    ax3.grid(True, alpha=0.3)
    ax3.legend()
    
    # Plot 4: Phase Portrait (Velocity vs Altitude)
    ax4 = axes[1, 1]
    for i, result in enumerate(results_list):
        ax4.plot(result['altitude'], result['velocity'], 
                label=result['controller_name'], 
                color=colors[i % len(colors)], linewidth=2)
        # Mark start and end points
        ax4.scatter(result['altitude'][0], result['velocity'][0], 
                   color=colors[i % len(colors)], s=100, marker='o', 
                   edgecolor='black', zorder=5)
        ax4.scatter(result['altitude'][-1], result['velocity'][-1], 
                   color=colors[i % len(colors)], s=100, marker='X', 
                   edgecolor='black', zorder=5)
    ax4.set_xlabel('Altitude (m)', fontsize=11)
    ax4.set_ylabel('Velocity (m/s)', fontsize=11)
    ax4.set_title('Phase Portrait (○ Start, X Land)', fontsize=12, fontweight='bold')
    ax4.grid(True, alpha=0.3)
    ax4.legend()
    ax4.axvline(x=0, color='black', linestyle='--', linewidth=1, alpha=0.5)
    ax4.axhline(y=0, color='black', linestyle='--', linewidth=1, alpha=0.5)
    
    plt.tight_layout()
    plt.savefig('rocket_landing_comparison.png', dpi=300, bbox_inches='tight')
    print(f"\n{'='*60}")
    print(f"Plot saved: rocket_landing_comparison.png")
    print(f"{'='*60}")
    plt.show()


def print_comparison_table(results_list: list):
    """
    Print comparison table of all control strategies
    
    Args:
        results_list: List of result dictionaries
    """
    print(f"\n{'='*80}")
    print(f"{'PERFORMANCE COMPARISON TABLE':^80}")
    print(f"{'='*80}")
    print(f"{'Controller':<35} {'Time(s)':<10} {'V_land(m/s)':<12} {'Fuel(N·s)':<12} {'Success':<10}")
    print(f"{'-'*80}")
    
    for result in results_list:
        success_symbol = '✓' if result['success'] else '✗'
        print(f"{result['controller_name']:<35} "
              f"{result['landing_time']:<10.2f} "
              f"{result['landing_velocity']:<12.2f} "
              f"{result['fuel_used']:<12.2f} "
              f"{success_symbol:<10}")
    
    print(f"{'='*80}\n")


def main():
    """
    Main simulation: Compare Bang-Bang vs PID control strategies
    """
    print("\n" + "="*80)
    print(" "*20 + "ROCKET SOFT LANDING SIMULATION")
    print(" "*15 + "2nd Order ODE + Thrust Limits + Event Detection")
    print("="*80)
    
    # Initialize rocket parameters
    params = RocketParameters(
        m=1000.0,           # 1000 kg rocket
        g=9.81,             # Earth gravity
        T_max=15000.0,      # 15 kN max thrust
        z0=1000.0,          # Start at 1 km altitude
        v0=-50.0,           # Falling at 50 m/s
        target_velocity=-2.0  # Target soft landing at 2 m/s
    )
    
    # List to store all results
    all_results = []
    
    # SCENARIO 1: Bang-Bang Controller (aggressive threshold)
    print("\n" + "="*80)
    print("SCENARIO 1: Bang-Bang Controller (Aggressive)")
    print("="*80)
    controller1 = BangBangController(params, v_threshold=-15.0)
    sim1 = RocketLandingSimulator(params, controller1)
    result1 = sim1.simulate(t_max=50.0, dt=0.1)
    all_results.append(result1)
    
    # SCENARIO 2: Bang-Bang Controller (conservative threshold)
    print("\n" + "="*80)
    print("SCENARIO 2: Bang-Bang Controller (Conservative)")
    print("="*80)
    controller2 = BangBangController(params, v_threshold=-5.0)
    sim2 = RocketLandingSimulator(params, controller2)
    result2 = sim2.simulate(t_max=50.0, dt=0.1)
    all_results.append(result2)
    
    # SCENARIO 3: PID Controller (baseline tuning)
    print("\n" + "="*80)
    print("SCENARIO 3: PID Controller (Baseline)")
    print("="*80)
    controller3 = PIDController(params, Kp=500, Ki=50, Kd=1000)
    sim3 = RocketLandingSimulator(params, controller3)
    result3 = sim3.simulate(t_max=50.0, dt=0.1)
    all_results.append(result3)
    
    # SCENARIO 4: PID Controller (aggressive tuning)
    print("\n" + "="*80)
    print("SCENARIO 4: PID Controller (Aggressive)")
    print("="*80)
    controller4 = PIDController(params, Kp=800, Ki=100, Kd=1500)
    sim4 = RocketLandingSimulator(params, controller4)
    result4 = sim4.simulate(t_max=50.0, dt=0.1)
    all_results.append(result4)
    
    # Print comparison table
    print_comparison_table(all_results)
    
    # Create comparison plots
    plot_comparison(all_results)
    
    # Analysis and conclusions
    print("\n" + "="*80)
    print(" "*25 + "ANALYSIS & CONCLUSIONS")
    print("="*80)
    print("""
BANG-BANG CONTROL:
✓ Advantages:
  - Simple to implement (just threshold logic)
  - Fast response to velocity changes
  - Computationally cheap
  
✗ Disadvantages:
  - Oscillations around threshold (chatter)
  - Inefficient fuel usage (always max or zero)
  - Hard landing possible if threshold poorly chosen
  - No smooth control

PID CONTROL:
✓ Advantages:
  - Smooth thrust variation (fuel efficient)
  - Better landing accuracy
  - Adaptable via tuning (Kp, Ki, Kd)
  - Reduces oscillations
  
✗ Disadvantages:
  - Requires tuning (trial and error)
  - More complex implementation
  - May overshoot if poorly tuned

RECOMMENDATION:
For real rocket landing systems (e.g., SpaceX Falcon 9), PID or advanced
model predictive control (MPC) is preferred due to:
1. Fuel efficiency (critical for space missions)
2. Smooth landing (reduces structural stress)
3. Better handling of disturbances
4. More precise control
    """)
    
    print("\n" + "="*80)
    print("SIMULATION COMPLETE ✓")
    print("="*80 + "\n")


if __name__ == "__main__":
    main()
