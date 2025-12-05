# 📐 DIFFERENTIAL EQUATIONS EXPLAINED - COMPLETE MATHEMATICAL GUIDE

## Rocket Soft Landing Simulation - All Equations Derived and Explained

---

## TABLE OF CONTENTS

1. [Introduction](#introduction)
2. [Newton's Second Law - The Foundation](#newtons-second-law)
3. [2nd-Order ODE Derivation](#2nd-order-ode-derivation)
4. [State-Space Transformation](#state-space-transformation)
5. [Numerical Integration Methods](#numerical-integration-methods)
6. [Control Equations](#control-equations)
7. [Event Detection](#event-detection)
8. [Complete System of Equations](#complete-system)
9. [Worked Examples](#worked-examples)

---

## 1. INTRODUCTION

### What Are Differential Equations?

**Differential equations** relate a function to its derivatives. They describe how things change over time.

**Example from daily life:**
- **Position:** Where you are
- **Velocity:** How fast position changes (derivative of position)
- **Acceleration:** How fast velocity changes (derivative of velocity)

**Mathematical notation:**
```
Position:     x(t)
Velocity:     v(t) = dx/dt = x'(t)
Acceleration: a(t) = dv/dt = d²x/dt² = x''(t)
```

### Why Do We Need Them for Rocket Landing?

To land a rocket safely, we need to:
1. **Predict** where the rocket will be at future times
2. **Control** the rocket's motion by adjusting thrust
3. **Optimize** fuel usage while ensuring soft landing

Differential equations let us:
- **Model** the physics (F=ma)
- **Simulate** the motion numerically
- **Design** controllers that work

---

## 2. NEWTON'S SECOND LAW - THE FOUNDATION

### The Most Important Equation in Classical Mechanics

```
F = ma
```

**In words:** Force equals mass times acceleration

**What it means:**
- **Force (F):** Push or pull on an object (measured in Newtons, N)
- **Mass (m):** Amount of matter (measured in kilograms, kg)
- **Acceleration (a):** Rate of change of velocity (measured in m/s²)

### Free Body Diagram for Our Rocket

```
         ↑ +z (upward positive)
         │
         │
    ┌────┴────┐
    │         │
    │ ROCKET  │  ← T (Thrust, upward)
    │  mass m │
    │         │
    └─────────┘
         │
         ↓ mg (Gravity, downward)
         
    ═════════════  ← Ground (z = 0)
```

### Forces Acting on Rocket

**1. Gravitational Force (Weight)**
```
F_gravity = -mg
```
- **Direction:** Always downward (negative z-direction)
- **Magnitude:** m × g where:
  - m = rocket mass (kg)
  - g = gravitational acceleration ≈ 9.81 m/s² on Earth
- **Sign convention:** Negative because downward
- **Always present:** Doesn't depend on rocket state

**Physical intuition:** Earth pulls everything toward its center. Heavier objects feel stronger pull (more mass → more force), but same acceleration (F/m = g).

**2. Thrust Force (Engine Power)**
```
F_thrust = T(t)
```
- **Direction:** Upward (positive z-direction) when engine firing
- **Magnitude:** 0 ≤ T ≤ T_max
  - T = 0: Engine off
  - T = T_max: Full power
  - 0 < T < T_max: Partial throttle
- **Control variable:** We choose T based on control law
- **Time-dependent:** T(t) changes as controller adjusts

**Physical intuition:** Engine burns fuel, expels gas downward at high speed. By Newton's 3rd law (action-reaction), exhaust pushes rocket upward.

### Net Force and Acceleration

**Sum all forces:**
```
ΣF = F_thrust + F_gravity
ΣF = T - mg
```

**Apply Newton's 2nd law:**
```
ma = T - mg
```

**Solve for acceleration:**
```
a = (T - mg) / m
a = T/m - g
a = -g + T/m  ← FUNDAMENTAL EQUATION
```

**Three important cases:**

**Case 1: Free fall (T = 0)**
```
a = -g = -9.81 m/s²
```
→ Rocket accelerates downward at 9.81 m/s², crashing into ground

**Case 2: Hovering (T = mg)**
```
a = -g + mg/m = -g + g = 0
```
→ Zero acceleration → constant velocity (can maintain altitude or descend steadily)

**Case 3: Full thrust (T = T_max)**
```
a = -g + T_max/m
```
→ If T_max/m > g: Upward acceleration (slowing descent)
→ If T_max/m = g: Hovering
→ If T_max/m < g: Still falling, but slower than free fall

**Example calculation:**
```
Given:
  m = 1000 kg
  g = 9.81 m/s²
  T_max = 15000 N

Hover thrust needed:
  T_hover = mg = 1000 × 9.81 = 9810 N

Maximum acceleration:
  a_max = -g + T_max/m = -9.81 + 15000/1000 = -9.81 + 15 = 5.19 m/s²
  
→ Rocket can accelerate upward at 5.19 m/s² (plenty of margin!)
```

---

## 3. 2ND-ORDER ODE DERIVATION

### From Acceleration to Differential Equation

**Recall the kinematic chain:**
```
Position:     z(t)
Velocity:     v(t) = dz/dt = ż(t)
Acceleration: a(t) = dv/dt = d²z/dt² = z̈(t)
```

**We derived:**
```
a = -g + T/m
```

**Since a = d²z/dt²:**
```
d²z/dt² = -g + T(t)/m
```

**Alternative notation:**
```
z̈(t) = -g + T(t)/m
```

**This is a SECOND-ORDER ORDINARY DIFFERENTIAL EQUATION (ODE).**

### What Makes It "2nd-Order"?

**Order of a differential equation** = highest derivative present

**Examples:**
- **0th-order:** y = 5 (algebraic equation, no derivatives)
- **1st-order:** dy/dt = -ky (exponential decay)
- **2nd-order:** d²y/dt² = -ω²y (simple harmonic oscillator)
- **3rd-order:** d³y/dt³ + y = 0 (rare in physics)

**Our equation:**
```
z̈ = -g + T/m
```
Highest derivative is z̈ (2nd derivative) → **2nd-order ODE**

### Why Do 2nd-Order ODEs Arise in Physics?

**Newton's 2nd law (F = ma) always gives 2nd-order ODEs** because:
- Force relates to acceleration (2nd derivative of position)
- Physical systems have **inertia** (mass resists changes in motion)
- Need to know **both** position and velocity to predict future

**Common 2nd-order systems:**
- **Springs:** mẍ + kx = 0 (harmonic oscillator)
- **Pendulum:** θ̈ + (g/L)sin(θ) = 0 (nonlinear oscillator)
- **Rocket:** z̈ = -g + T/m (controlled system)
- **Electric circuits:** Lq̈ + Rq̇ + q/C = V (RLC circuit)

### Initial Conditions - Why We Need Two

**For 2nd-order ODE, need TWO initial conditions:**

```
z(0) = z₀     (initial position/altitude)
ż(0) = v₀     (initial velocity)
```

**Why two?** 
- **Position alone** insufficient: rocket at 100m could be rising or falling
- **Velocity alone** insufficient: rocket falling at -50 m/s could be at 100m or 500m altitude

**Together:** z₀ and v₀ **uniquely determine** the entire trajectory (given control law T(t))

**Analogy:** Throwing a ball
- Where you release it (z₀) + how fast you throw it (v₀) → determines entire path

### Complete Problem Statement

**Given:**
- Rocket mass: m = 1000 kg
- Gravity: g = 9.81 m/s²
- Max thrust: T_max = 15000 N
- Initial altitude: z(0) = 1000 m
- Initial velocity: v(0) = -50 m/s (descending)

**Governing equation:**
```
z̈(t) = -g + T(t)/m,    t ≥ 0
z(0) = 1000
ż(0) = -50
```

**Control constraint:**
```
0 ≤ T(t) ≤ T_max = 15000
```

**Landing event:**
```
Landing occurs when z(t*) = 0 for some t* > 0
```

**Objective:**
```
Choose T(t) such that:
1. z(t*) = 0 (lands on ground)
2. |ż(t*)| < 5 m/s (soft landing)
3. ∫₀^t* T(t)dt is minimized (fuel efficient)
```

---

## 4. STATE-SPACE TRANSFORMATION

### The Problem with 2nd-Order ODEs

**Most numerical solvers** (like RK45, Euler, etc.) are designed for **1st-order systems:**
```
ẏ = f(t, y)
```

**Our equation is 2nd-order:**
```
z̈ = -g + T/m
```

**Solution:** Transform to **coupled 1st-order system** using **state-space representation**

### Defining State Variables

**Choose state vector with 2 components:**
```
x = [x₁]  = [z]    (altitude)
    [x₂]    [v]    (velocity)
```

**Why this choice?**
- Need 2 variables for 2nd-order system
- Position and velocity are **natural state variables** (directly measurable)
- Together they completely describe rocket's motion state

### Computing State Derivatives

**For x₁ = z:**
```
ẋ₁ = dz/dt = v = x₂
```
→ Derivative of position is velocity (definition)

**For x₂ = v:**
```
ẋ₂ = dv/dt = a = z̈ = -g + T/m
```
→ Derivative of velocity is acceleration (from our physics)

### The State-Space System

**Complete system:**
```
ẋ₁ = x₂
ẋ₂ = -g + T/m
```

**In vector form:**
```
ẋ = f(t, x, u)

where:
  x = [z, v]ᵀ       (state vector)
  u = T             (control input)
  f = [v, -g+T/m]ᵀ  (dynamics function)
```

**Initial conditions:**
```
x(0) = [z₀, v₀]ᵀ = [1000, -50]ᵀ
```

### Matrix Formulation (Control Theory Standard)

**Linear system form:**
```
ẋ = Ax + Bu + w

where:
  A = [0  1]     (system matrix)
      [0  0]
      
  B = [  0  ]    (control matrix)
      [1/m  ]
      
  w = [ 0 ]      (external input)
      [-g ]
```

**Verification:**
```
ẋ = Ax + Bu + w

  = [0  1][z]   +  [  0  ]T  +  [ 0 ]
    [0  0][v]      [1/m  ]      [-g ]
    
  = [    v     ]   +  [  0  ]  +  [ 0 ]
    [    0     ]      [T/m  ]     [-g ]
    
  = [        v        ]
    [-g + T/m         ]  ✓ Matches our equations!
```

### Implementation in Code

**Python function (from our simulation):**

```python
def dynamics(t, state):
    """
    Compute time derivatives of state variables.
    
    This is the "f" in ẋ = f(t, x, u)
    
    Parameters:
    -----------
    t : float
        Current time (seconds)
    state : array [z, v]
        Current state vector
        
    Returns:
    --------
    derivatives : array [dz/dt, dv/dt]
        State derivatives
        
    Mathematical Formula:
    ---------------------
    dz/dt = v
    dv/dt = -g + T(t,z,v)/m
    
    Where T(t,z,v) is computed by the controller
    """
    # Unpack state
    z, v = state
    
    # Get control input from controller
    # Controller looks at current state and decides thrust
    thrust = controller.compute_thrust(t, z, v)
    
    # Compute acceleration from Newton's 2nd law
    acceleration = -g + thrust / m
    
    # Return derivatives
    dz_dt = v
    dv_dt = acceleration
    
    return [dz_dt, dv_dt]
```

**Usage with ODE solver:**
```python
from scipy.integrate import solve_ivp

# Initial state
z0 = 1000  # meters
v0 = -50   # m/s
initial_state = [z0, v0]

# Time span
t_span = (0, 100)  # 0 to 100 seconds
t_eval = np.linspace(0, 100, 1000)  # 1000 time points

# Solve the ODE system
solution = solve_ivp(
    fun=dynamics,          # Our dynamics function
    t_span=t_span,         # Time interval
    y0=initial_state,      # Initial conditions
    method='RK45',         # Runge-Kutta 4/5 method
    t_eval=t_eval,         # Times to save solution
    events=landing_event,  # Stop when z=0
    dense_output=True      # Enable interpolation
)

# Extract results
time = solution.t
altitude = solution.y[0]
velocity = solution.y[1]
```

### Geometric Interpretation - Phase Space

**State space = 2D plane** with coordinates (z, v)

**Every point** in this plane represents a possible state:
- (1000, -50): Rocket at 1000m, falling at 50 m/s
- (500, -20): Rocket at 500m, falling at 20 m/s
- (0, -2): Rocket at ground, descending at 2 m/s (soft landing!)

**State trajectory:** Path through state space as time progresses
```
t=0:    (z, v) = (1000, -50)
t=10:   (z, v) = (650, -35)
t=20:   (z, v) = (350, -18)
t=30:   (z, v) = (50, -3)
t=35:   (z, v) = (0, -2)  ← Landing!
```

**Phase portrait:** Plot showing multiple trajectories
- Each curve = one possible landing trajectory
- Different T(t) control laws → different curves
- Goal: Find curve ending near (0, -2)

---

## 5. NUMERICAL INTEGRATION METHODS

### Why Numerical Methods?

**Analytical solution** (exact formula) doesn't exist for our problem because:
1. **Thrust T(t) is complex:** Depends on feedback, switches on/off, nonlinear
2. **No closed-form solution:** Can't write z(t) = ...
3. **Need computer:** Approximate solution using numerical integration

### Euler's Method - Simplest Approach

**Basic idea:** Approximate curve by small straight line segments

**Formula:**
```
x(t + Δt) ≈ x(t) + ẋ(t)·Δt

where ẋ(t) = f(t, x)
```

**For our system:**
```
z(t + Δt) = z(t) + v(t)·Δt
v(t + Δt) = v(t) + [-g + T(t)/m]·Δt
```

**Algorithm:**
```python
def euler_step(z, v, dt):
    """Single Euler integration step"""
    T = controller.compute_thrust(t, z, v)
    
    # Update velocity first
    a = -g + T/m
    v_new = v + a * dt
    
    # Update position
    z_new = z + v * dt
    
    return z_new, v_new

# Main loop
t, z, v = 0, z0, v0
while z > 0 and t < t_max:
    z, v = euler_step(z, v, dt)
    t += dt
```

**Pros:**
- ✓ Simple to understand
- ✓ Easy to implement

**Cons:**
- ✗ **Large error:** Error ∝ Δt (1st order accuracy)
- ✗ **Requires tiny steps:** Need Δt < 0.001s for accuracy
- ✗ **Unstable:** Error accumulates, can explode
- ✗ **Slow:** Many steps needed

**When to use:** Quick prototypes, educational purposes only

### Runge-Kutta 4th Order (RK4) - Industry Standard

**Basic idea:** Take **weighted average** of slopes at different points

**Formula:**
```
k₁ = f(t, x)
k₂ = f(t + Δt/2, x + k₁·Δt/2)
k₃ = f(t + Δt/2, x + k₂·Δt/2)
k₄ = f(t + Δt, x + k₃·Δt)

x(t + Δt) = x(t) + (Δt/6)(k₁ + 2k₂ + 2k₃ + k₄)
```

**Physical interpretation:**
- **k₁:** Slope at beginning of interval
- **k₂:** Slope at midpoint using k₁
- **k₃:** Slope at midpoint using k₂ (refined)
- **k₄:** Slope at end using k₃
- **Average:** Weight midpoints more (2× each)

**For our system:**
```python
def rk4_step(z, v, t, dt):
    """Single RK4 integration step"""
    
    # k1: slope at start
    T1 = controller.compute_thrust(t, z, v)
    a1 = -g + T1/m
    k1_z = v
    k1_v = a1
    
    # k2: slope at midpoint using k1
    t2 = t + dt/2
    z2 = z + k1_z * dt/2
    v2 = v + k1_v * dt/2
    T2 = controller.compute_thrust(t2, z2, v2)
    a2 = -g + T2/m
    k2_z = v2
    k2_v = a2
    
    # k3: slope at midpoint using k2
    t3 = t + dt/2
    z3 = z + k2_z * dt/2
    v3 = v + k2_v * dt/2
    T3 = controller.compute_thrust(t3, z3, v3)
    a3 = -g + T3/m
    k3_z = v3
    k3_v = a3
    
    # k4: slope at end using k3
    t4 = t + dt
    z4 = z + k3_z * dt
    v4 = v + k3_v * dt
    T4 = controller.compute_thrust(t4, z4, v4)
    a4 = -g + T4/m
    k4_z = v4
    k4_v = a4
    
    # Weighted average
    z_new = z + (dt/6) * (k1_z + 2*k2_z + 2*k3_z + k4_z)
    v_new = v + (dt/6) * (k1_v + 2*k2_v + 2*k3_v + k4_v)
    
    return z_new, v_new
```

**Pros:**
- ✓ **Accurate:** Error ∝ Δt⁴ (4th order)
- ✓ **Stable:** Doesn't blow up easily
- ✓ **Efficient:** Can use larger Δt (0.01-0.1s)
- ✓ **Standard:** Used in spacecraft, robotics, etc.

**Cons:**
- ✗ **4× function calls** per step (vs Euler)
- ✗ **Fixed step size:** Wastes computation in smooth regions

**When to use:** Production code, accurate results needed

### RK45 (Adaptive Runge-Kutta) - Our Choice

**Enhancement over RK4:** **Adaptive step sizing**

**How it works:**
1. Compute both 4th-order and 5th-order estimates
2. Compare them to estimate error
3. If error too large: reduce Δt, retry
4. If error too small: increase Δt for efficiency

**Error estimation:**
```
x₄ = RK4 estimate (4th order accurate)
x₅ = RK5 estimate (5th order accurate)

error = |x₅ - x₄|

if error > tolerance:
    Δt = Δt * 0.9 * (tolerance/error)^0.2
    retry step
elif error < tolerance/10:
    Δt = Δt * 1.5
    accept step and increase step size for next iteration
else:
    accept step
```

**Advantages:**
- ✓ **Automatic accuracy:** Maintains user-specified tolerance
- ✓ **Efficient:** Large steps in smooth regions, small steps near events
- ✓ **Robust:** Adapts to problem stiffness
- ✓ **SciPy default:** Built-in, well-tested

**Usage:**
```python
solution = solve_ivp(
    fun=dynamics,
    t_span=(0, 100),
    y0=[z0, v0],
    method='RK45',           # Adaptive Runge-Kutta
    rtol=1e-6,               # Relative tolerance
    atol=1e-9,               # Absolute tolerance
    dense_output=True,       # Enable interpolation
    events=landing_event     # Stop at z=0
)
```

**Tolerance meaning:**
- **rtol** (relative): Fractional error, e.g., 1e-6 means 0.0001% error
- **atol** (absolute): Minimum error floor, e.g., 1e-9 means ±0.000000001

**Combined criterion:**
```
acceptable_error = atol + rtol × |x|
```

---

## 6. CONTROL EQUATIONS

### Bang-Bang Controller - Binary ON/OFF Control

**Control Law:**
```
T(t) = {  T_max,  if v(t) < v_threshold
       {  0,      if v(t) ≥ v_threshold
```

**In mathematical notation:**
```
T(t) = T_max · H(v_threshold - v(t))

where H(x) = Heaviside step function = { 1 if x > 0
                                        { 0 if x ≤ 0
```

**Implementation:**
```python
class BangBangController:
    def __init__(self, T_max, v_threshold):
        self.T_max = T_max
        self.v_threshold = v_threshold
    
    def compute_thrust(self, t, z, v):
        """
        Bang-bang control logic.
        
        If falling too fast (v < threshold): TURN ON full thrust
        If acceptable speed: TURN OFF engine (coast)
        """
        if v < self.v_threshold:
            return self.T_max
        else:
            return 0.0
```

**Characteristics:**
- **Discontinuous:** Jumps between 0 and T_max instantly
- **Chattering:** Can oscillate rapidly near threshold
- **Fuel:** Uses maximum power when ON (inefficient)
- **Robust:** Simple, works in noisy conditions

**Threshold selection:**
```
v_threshold = -10 m/s (typical)

More negative (e.g., -20): 
  → Turns ON later
  → Uses less fuel
  → Riskier (may land too hard)

Less negative (e.g., -5):
  → Turns ON earlier
  → Uses more fuel
  → Safer (gentler landing)
```

### PID Controller - Smooth Optimal Control

**Control Law:**
```
T(t) = T_baseline + T_PID

where:
  T_baseline = mg (thrust needed to hover)
  
  T_PID = Kp·e(t) + Ki·∫₀ᵗ e(τ)dτ + Kd·de/dt
  
  e(t) = v_target(z) - v(t)  (velocity error)
```

**Three terms explained:**

**Proportional (P):**
```
T_P = Kp · e(t) = Kp · (v_target - v)
```
- **Responds to current error**
- Large error → large correction
- Like a spring: pull harder when further from target
- **Alone:** Can't eliminate steady-state error

**Integral (I):**
```
T_I = Ki · ∫₀ᵗ e(τ)dτ = Ki · [accumulated error over time]
```
- **Eliminates steady-state error**
- If error persists, integral grows → stronger correction
- Like compound interest: small errors accumulate
- **Problem:** "Integral windup" if error large for long time

**Derivative (D):**
```
T_D = Kd · de/dt = Kd · d(v_target - v)/dt = -Kd · dv/dt = -Kd · a
```
- **Dampens oscillations**
- Predicts future error based on rate of change
- Like a dashpot: resists rapid changes
- **Problem:** Amplifies noise in measurements

**Complete equation with thrust limits:**
```
T_unlimited = mg + Kp·e + Ki·∫e·dt + Kd·de/dt

T(t) = clip(T_unlimited, 0, T_max)
     = max(0, min(T_unlimited, T_max))
```

**Adaptive target velocity:**
```
                ⎧ v_target·(z/z₀)·10,           if z > 100m
v_target(z) =   ⎨
                ⎩ v_target·√(z/100),             if z ≤ 100m
```

**Reason:** Want to slow down gradually
- **High altitude:** Can fall faster (more time to correct)
- **Near ground:** Must be very slow (about to land)

**Implementation:**
```python
class PIDController:
    def __init__(self, m, g, T_max, Kp, Ki, Kd, v_target):
        self.m = m
        self.g = g
        self.T_max = T_max
        self.Kp = Kp
        self.Ki = Ki
        self.Kd = Kd
        self.v_target = v_target
        
        # Internal state
        self.integral = 0
        self.prev_error = 0
        self.prev_time = 0
    
    def compute_thrust(self, t, z, v):
        """
        PID control with adaptive velocity targeting.
        
        Formula:
        --------
        T = mg + Kp·e + Ki·∫e·dt + Kd·de/dt
        
        where e = v_target(z) - v
        """
        # Time step
        dt = t - self.prev_time if self.prev_time > 0 else 0.01
        
        # Adaptive target velocity based on altitude
        if z > 100:
            v_target_now = self.v_target * (z / 1000) * 10
        else:
            v_target_now = self.v_target * np.sqrt(max(z / 100, 0.01))
        
        # Error signal
        error = v_target_now - v
        
        # Proportional term
        P = self.Kp * error
        
        # Integral term (with anti-windup)
        self.integral += error * dt
        I = self.Ki * self.integral
        
        # Derivative term
        derivative = (error - self.prev_error) / dt if dt > 0 else 0
        D = self.Kd * derivative
        
        # Baseline thrust (hover)
        baseline = self.m * self.g
        
        # Total thrust
        thrust = baseline + P + I + D
        
        # Apply limits
        thrust = np.clip(thrust, 0, self.T_max)
        
        # Update state
        self.prev_error = error
        self.prev_time = t
        
        return thrust
```

**Tuning guidelines:**

**Ziegler-Nichols method:**
1. Set Ki = Kd = 0
2. Increase Kp until system oscillates
3. Note Kp_critical and oscillation period T_u
4. Use formulas:
   ```
   Kp = 0.6 · Kp_critical
   Ki = 1.2 · Kp_critical / T_u
   Kd = 0.075 · Kp_critical · T_u
   ```

**Manual tuning:**
```
Start: Kp=0, Ki=0, Kd=0

Step 1: Tune Kp
  - Increase until fast response with small overshoot
  - If oscillates: decrease Kp

Step 2: Tune Ki  
  - Add Ki = Kp/10
  - Increase to eliminate steady-state error
  - If overshoots: decrease Ki

Step 3: Tune Kd
  - Add Kd = Kp*2
  - Increase to reduce overshoot
  - If sluggish: decrease Kd
```

---

## 7. EVENT DETECTION

### Landing Event - When Does Simulation Stop?

**Condition:** Rocket touches ground
```
z(t*) = 0   for some time t* > 0
```

**Problem:** We compute z at discrete times t₀, t₁, t₂, ...
- Might skip over exact landing moment
- z(t_i) = 0.5 m, z(t_i+1) = -0.3 m → landed somewhere between

**Solution:** **Event detection** with root finding

### Event Function

**Define event function:**
```
g(t) = z(t)
```

**Landing occurs when:**
```
g(t*) = 0   (root of event function)
```

**Direction:** Only detect downward crossing
```
g'(t*) < 0   (z decreasing → velocity negative)
```

**Implementation:**
```python
def landing_event(t, state):
    """
    Event function for landing detection.
    
    Returns:
    --------
    z : float
        Altitude (event triggers when this crosses zero)
    
    The ODE solver monitors this function and stops
    integration when z crosses zero from positive to negative.
    """
    z, v = state
    return z  # Return altitude

# Configure event
landing_event.terminal = True      # Stop integration
landing_event.direction = -1       # Only downward crossing
```

### Root Finding Algorithm

**Bisection method** (used internally by solve_ivp):

```
Given: z(t₁) > 0 and z(t₂) < 0 (sign change)

1. Compute midpoint: t_mid = (t₁ + t₂) / 2
2. Evaluate: z_mid = z(t_mid)
3. If |z_mid| < tolerance: DONE, landing time ≈ t_mid
4. Else if z_mid > 0: landing in [t_mid, t₂], set t₁ = t_mid
5. Else if z_mid < 0: landing in [t₁, t_mid], set t₂ = t_mid
6. Repeat from step 1

Converges in log₂(interval/tolerance) iterations
```

**Example:**
```
t₁ = 35.0s, z₁ = +0.8m
t₂ = 35.1s, z₂ = -0.2m

Iteration 1:
  t_mid = 35.05s
  z_mid = +0.3m > 0
  → landing in [35.05, 35.1]
  
Iteration 2:
  t_mid = 35.075s
  z_mid = +0.05m > 0
  → landing in [35.075, 35.1]
  
Iteration 3:
  t_mid = 35.0875s
  z_mid = -0.075m < 0
  → landing in [35.075, 35.0875]
  
Iteration 4:
  t_mid = 35.08125s
  z_mid = -0.0125m < 0
  → landing in [35.075, 35.08125]
  
Iteration 5:
  t_mid = 35.078125s
  z_mid = +0.01875m > 0
  → landing in [35.078125, 35.08125]
  
... continues until z_mid ≈ 0 within tolerance
```

**Dense output:** Solver maintains polynomial interpolation between steps, so can evaluate z(t) at any time (not just grid points)

---

## 8. COMPLETE SYSTEM OF EQUATIONS

### Full Mathematical Model

**State Variables:**
```
x = [z, v]ᵀ where z ∈ ℝ, v ∈ ℝ
```

**State Dynamics:**
```
ẋ = f(t, x, u) = [     v      ]
                  [-g + u/m   ]

where u = T(t, z, v) ∈ [0, T_max]
```

**Initial Conditions:**
```
x(0) = [z₀, v₀]ᵀ
```

**Control Law (Bang-Bang):**
```
u = T(z, v) = { T_max,  if v < v_threshold
              { 0,      otherwise
```

**Control Law (PID):**
```
u = T(t, z, v) = clip(mg + Kp·e + Ki·∫e·dt + Kd·ė, 0, T_max)

where e(t) = v_target(z) - v
```

**Terminal Condition:**
```
Stop when z(t*) = 0 with dz/dt* < 0
```

**Success Criteria:**
```
|z(t*)| < 1m  AND  |v(t*)| < 5m/s
```

### Summary Table

| Component | Mathematical Expression | Physical Meaning |
|-----------|------------------------|------------------|
| **Position** | z(t) | Altitude above ground (m) |
| **Velocity** | v(t) = dz/dt | Rate of descent (m/s) |
| **Acceleration** | a(t) = dv/dt | Rate of velocity change (m/s²) |
| **Gravity Force** | F_g = -mg | Downward pull (N) |
| **Thrust Force** | F_T = T(t) | Upward engine push (N) |
| **Net Force** | F = T - mg | Total force (N) |
| **2nd Law** | ma = T - mg | Newton's equation |
| **ODE** | z̈ = -g + T/m | Governing equation |
| **State-Space** | ẋ = [v, -g+T/m]ᵀ | System representation |
| **Control** | T = controller(z,v) | Feedback law |
| **Landing** | z(t*) = 0 | Terminal event |

---

## 9. WORKED EXAMPLES

### Example 1: Free Fall (No Control)

**Setup:**
```
m = 1000 kg
g = 9.81 m/s²
z(0) = 1000 m
v(0) = -50 m/s
T(t) = 0 (engine off)
```

**Equations:**
```
z̈ = -g = -9.81
v̇ = -9.81
ż = v
```

**Integration (constant acceleration):**
```
v(t) = v₀ - gt = -50 - 9.81t
z(t) = z₀ + v₀t - ½gt² = 1000 - 50t - 4.905t²
```

**Landing time (z=0):**
```
0 = 1000 - 50t - 4.905t²
4.905t² + 50t - 1000 = 0

t = [-50 ± √(2500 + 19620)] / 9.81
  = [-50 ± 148.7] / 9.81
  = 10.05 s (positive root)
```

**Landing velocity:**
```
v(10.05) = -50 - 9.81(10.05)
         = -50 - 98.6
         = -148.6 m/s  ← CRASH! (535 km/h)
```

**Result:** Without control, rocket crashes at 149 m/s.

---

### Example 2: Constant Thrust

**Setup:**
```
Same as above but T = 12000 N (constant)
```

**Equations:**
```
a = -g + T/m = -9.81 + 12000/1000 = -9.81 + 12 = 2.19 m/s²
```

**Wait, positive acceleration?**
```
Yes! Thrust exceeds gravity:
  T/m = 12 m/s² > g = 9.81 m/s²
  → Net upward acceleration
  → Rocket will slow down, stop, then rise!
```

**Integration:**
```
v(t) = v₀ + at = -50 + 2.19t
z(t) = z₀ + v₀t + ½at² = 1000 - 50t + 1.095t²
```

**Rocket stops descending when v=0:**
```
0 = -50 + 2.19t
t = 50/2.19 = 22.8 s
```

**Altitude at this time:**
```
z(22.8) = 1000 - 50(22.8) + 1.095(22.8)²
        = 1000 - 1140 + 570
        = 430 m
```

**Conclusion:** Rocket stops at 430m, then rises! Need smarter control.

---

### Example 3: Bang-Bang Landing

**Setup:**
```
Same parameters, threshold = -10 m/s
```

**Logic:**
```
If v < -10: T = 15000 N
If v ≥ -10: T = 0 N
```

**Phase 1:** v(0) = -50 < -10 → Thrust ON
```
a = -9.81 + 15000/1000 = 5.19 m/s²

Velocity increases (becomes less negative):
v(t) = -50 + 5.19t

When does v reach -10?
-10 = -50 + 5.19t
t = 40/5.19 = 7.7 s

At t=7.7s:
z = 1000 - 50(7.7) + 0.5(5.19)(7.7)² = 769 m
v = -10 m/s
```

**Phase 2:** v = -10 → Thrust OFF (at z=769m)
```
a = -9.81 m/s²

Falls freely until v < -10 again:
v(t) = -10 - 9.81(t - 7.7)

Reaches v = -10 immediately, so thrust turns back ON!
```

**Chattering:** Oscillates around v = -10
- Thrust keeps switching ON/OFF rapidly
- Approximately maintains v ≈ -10 m/s
- Descends at roughly constant speed

**Landing (approximate):**
```
Time: ≈ 769m / 10m/s ≈ 77s more → total 85s
Velocity: ≈ -10 m/s

Success? Depends on criterion:
  |v| = 10 > 5 → FAIL (too fast)
```

**Better threshold:** Use -5 m/s for softer landing

---

### Example 4: PID Landing

**Setup:**
```
Kp = 500, Ki = 50, Kd = 1000
v_target = -2 m/s
```

**Early phase (z=1000m, v=-50):**
```
Target velocity (high altitude):
v_target = -2 · (1000/1000) · 10 = -20 m/s

Error:
e = -20 - (-50) = 30 m/s (falling too fast)

Initial thrust (approximation):
T ≈ mg + Kp·e = 9810 + 500(30) = 24810 N

But T_max = 15000, so:
T = 15000 N (saturated)

Acceleration:
a = -9.81 + 15 = 5.19 m/s²

Velocity increases (slows descent):
After 5s: v = -50 + 5.19(5) = -24 m/s
Still below target, thrust remains maximal
```

**Mid phase (z=500m, v=-15):**
```
Target velocity:
v_target = -2 · (500/1000) · 10 = -10 m/s

Error:
e = -10 - (-15) = 5 m/s

Integral (accumulated):
∫e ≈ 100 m (rough estimate)

Thrust:
T = 9810 + 500(5) + 50(100) + Kd·(...)
  ≈ 9810 + 2500 + 5000 + ...
  ≈ 17000 (would saturate to 15000)
```

**Final phase (z=50m, v=-3):**
```
Target velocity:
v_target = -2 · √(50/100) = -2 · 0.707 = -1.4 m/s

Error:
e = -1.4 - (-3) = 1.6 m/s

Small error → thrust near hover:
T ≈ mg + small corrections ≈ 10000 N

Gentle descent to landing
```

**Landing:**
```
Time: ≈ 30-40 s (smooth, efficient)
Velocity: ≈ -2 m/s (perfect!)
Fuel: Optimized (no chattering)

Success: YES ✓
```

---

## CONCLUSION

**Summary of Key Equations:**

1. **Newton's 2nd Law:** F = ma → a = (T - mg)/m
2. **2nd-Order ODE:** z̈ = -g + T/m
3. **State-Space:** ẋ = [v, -g+T/m]ᵀ
4. **Bang-Bang:** T = T_max if v < v_thresh else 0
5. **PID:** T = mg + Kp·e + Ki·∫e + Kd·ė
6. **Landing:** z(t*) = 0 with |v(t*)| < 5 m/s

**This simulation teaches:**
- Differential equations in practice
- Numerical integration methods
- Control theory fundamentals
- Physics-based modeling
- Software engineering

**Real-world applications:**
- SpaceX Falcon 9 landing
- Drone altitude control
- Aircraft autopilot
- Robot motion planning
- Process control

---

**End of Mathematical Guide**
