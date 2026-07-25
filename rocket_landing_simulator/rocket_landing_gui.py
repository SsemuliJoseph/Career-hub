"""
ROCKET SOFT LANDING - INTERACTIVE GUI SIMULATOR
World-Class User Interface with Real-Time Visualization

Features:
- Interactive parameter controls
- Real-time simulation animation
- Multiple control strategies
- Performance dashboard
- 3D-style visualization
- Professional design

Author: Career Hub Project
Date: December 4, 2025
"""

import numpy as np
import matplotlib.pyplot as plt
from matplotlib.backends.backend_tkagg import FigureCanvasTkAgg
from matplotlib.figure import Figure
from matplotlib.animation import FuncAnimation
from scipy.integrate import solve_ivp
import tkinter as tk
from tkinter import ttk, messagebox
import threading
from dataclasses import dataclass
from typing import Optional
import time


# ============================================================================
# COLOR SCHEME - Professional Dark Theme
# ============================================================================
COLORS = {
    'bg_dark': '#1a1a2e',
    'bg_medium': '#16213e',
    'bg_light': '#0f3460',
    'accent_blue': '#00adb5',
    'accent_orange': '#ff6b35',
    'accent_green': '#4caf50',
    'accent_red': '#f44336',
    'text_light': '#ffffff',
    'text_dim': '#b0b0b0',
    'panel_bg': '#2a2a4e',
    'success': '#00e676',
    'warning': '#ffeb3b',
    'danger': '#ff1744',
    'grid': '#404060'
}


@dataclass
class RocketParameters:
    """Rocket system parameters"""
    m: float = 1000.0
    g: float = 9.81
    T_max: float = 15000.0
    z0: float = 1000.0
    v0: float = -50.0
    target_velocity: float = -2.0


class RocketController:
    """Base class for rocket controllers"""
    
    def __init__(self, params: RocketParameters):
        self.params = params
        self.thrust_history = []
        self.time_history = []
    
    def compute_thrust(self, t: float, z: float, v: float) -> float:
        raise NotImplementedError
    
    def reset(self):
        self.thrust_history = []
        self.time_history = []


class BangBangController(RocketController):
    """Bang-Bang ON/OFF controller"""
    
    def __init__(self, params: RocketParameters, threshold: float = -10.0):
        super().__init__(params)
        self.threshold = threshold
    
    def compute_thrust(self, t: float, z: float, v: float) -> float:
        thrust = self.params.T_max if v < self.threshold else 0.0
        self.thrust_history.append(thrust)
        self.time_history.append(t)
        return thrust


class PIDController(RocketController):
    """PID controller with adaptive targeting"""
    
    def __init__(self, params: RocketParameters, Kp: float = 500.0, 
                 Ki: float = 50.0, Kd: float = 1000.0):
        super().__init__(params)
        self.Kp = Kp
        self.Ki = Ki
        self.Kd = Kd
        self.integral_error = 0.0
        self.prev_error = 0.0
        self.prev_time = 0.0
    
    def compute_thrust(self, t: float, z: float, v: float) -> float:
        dt = t - self.prev_time if t > self.prev_time else 0.01
        
        # Adaptive target velocity
        if z > 100:
            v_target = self.params.target_velocity * (z / self.params.z0) * 10
        else:
            v_target = self.params.target_velocity * np.sqrt(max(z / 100, 0.01))
        
        error = v_target - v
        
        # PID terms
        P = self.Kp * error
        self.integral_error += error * dt
        I = self.Ki * self.integral_error
        D = self.Kd * (error - self.prev_error) / dt if dt > 0 else 0
        
        baseline = self.params.m * self.params.g
        thrust = baseline + P + I + D
        thrust = np.clip(thrust, 0, self.params.T_max)
        
        self.prev_error = error
        self.prev_time = t
        self.thrust_history.append(thrust)
        self.time_history.append(t)
        
        return thrust
    
    def reset(self):
        super().reset()
        self.integral_error = 0.0
        self.prev_error = 0.0
        self.prev_time = 0.0


class RocketSimulator:
    """Rocket landing simulator"""
    
    def __init__(self, params: RocketParameters, controller: RocketController):
        self.params = params
        self.controller = controller
        self.solution = None
        self.is_running = False
    
    def dynamics(self, t: float, state: np.ndarray) -> np.ndarray:
        z, v = state
        thrust = self.controller.compute_thrust(t, z, v)
        a = -self.params.g + thrust / self.params.m
        return [v, a]
    
    def landing_event(self, t: float, state: np.ndarray) -> float:
        return state[0]
    
    landing_event.terminal = True
    landing_event.direction = -1
    
    def simulate(self, t_max: float = 100.0, dt: float = 0.05):
        self.controller.reset()
        y0 = [self.params.z0, self.params.v0]
        t_span = (0, t_max)
        t_eval = np.arange(0, t_max, dt)
        
        self.is_running = True
        self.solution = solve_ivp(
            self.dynamics,
            t_span,
            y0,
            method='RK45',
            t_eval=t_eval,
            events=self.landing_event,
            dense_output=True,
            max_step=dt
        )
        self.is_running = False
        
        return self.get_results()
    
    def get_results(self):
        if self.solution is None:
            return None
        
        t = self.solution.t
        z = self.solution.y[0]
        v = self.solution.y[1]
        
        landing_time = t[-1]
        landing_velocity = v[-1]
        landing_altitude = z[-1]
        
        thrust_array = np.array(self.controller.thrust_history)
        time_array = np.array(self.controller.time_history)
        
        fuel_used = np.trapz(thrust_array, time_array) if len(thrust_array) > 1 else 0
        success = abs(landing_altitude) < 1.0 and abs(landing_velocity) < 5.0
        
        return {
            'time': t,
            'altitude': z,
            'velocity': v,
            'thrust': thrust_array,
            'thrust_time': time_array,
            'landing_time': landing_time,
            'landing_velocity': landing_velocity,
            'landing_altitude': landing_altitude,
            'fuel_used': fuel_used,
            'success': success
        }


class RocketLandingGUI:
    """World-class GUI for rocket landing simulation"""
    
    def __init__(self, root):
        self.root = root
        self.root.title("🚀 Rocket Soft Landing Simulator - World Class Edition")
        self.root.geometry("1400x900")
        self.root.configure(bg=COLORS['bg_dark'])
        
        # State
        self.params = RocketParameters()
        self.current_controller = None
        self.simulator = None
        self.results = None
        self.is_simulating = False
        self.animation = None
        
        # Setup GUI
        self.setup_styles()
        self.create_widgets()
        self.create_plots()
        
        # Initialize with PID controller
        self.controller_type.set("PID")
        self.on_controller_change()
    
    def setup_styles(self):
        """Configure ttk styles"""
        style = ttk.Style()
        style.theme_use('clam')
        
        # Button style
        style.configure('Accent.TButton',
                       background=COLORS['accent_blue'],
                       foreground=COLORS['text_light'],
                       borderwidth=0,
                       focuscolor='none',
                       font=('Segoe UI', 10, 'bold'),
                       padding=10)
        style.map('Accent.TButton',
                 background=[('active', COLORS['accent_orange'])])
        
        # Frame style
        style.configure('Dark.TFrame',
                       background=COLORS['bg_dark'])
        
        # Label style
        style.configure('Title.TLabel',
                       background=COLORS['bg_dark'],
                       foreground=COLORS['text_light'],
                       font=('Segoe UI', 16, 'bold'))
        
        style.configure('Subtitle.TLabel',
                       background=COLORS['bg_medium'],
                       foreground=COLORS['text_light'],
                       font=('Segoe UI', 11, 'bold'))
        
        style.configure('Info.TLabel',
                       background=COLORS['bg_medium'],
                       foreground=COLORS['text_dim'],
                       font=('Segoe UI', 9))
    
    def create_widgets(self):
        """Create all GUI widgets"""
        # Main container
        main_frame = ttk.Frame(self.root, style='Dark.TFrame')
        main_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # Left panel - Controls
        left_panel = self.create_control_panel(main_frame)
        left_panel.grid(row=0, column=0, sticky='nsew', padx=(0, 5))
        
        # Right panel - Visualization
        right_panel = self.create_visualization_panel(main_frame)
        right_panel.grid(row=0, column=1, sticky='nsew', padx=(5, 0))
        
        # Configure grid weights
        main_frame.grid_columnconfigure(0, weight=1, minsize=350)
        main_frame.grid_columnconfigure(1, weight=3, minsize=800)
        main_frame.grid_rowconfigure(0, weight=1)
    
    def create_control_panel(self, parent):
        """Create control panel with parameters"""
        panel = tk.Frame(parent, bg=COLORS['bg_medium'], relief=tk.RAISED, bd=2)
        
        # Header
        header = tk.Label(panel, text="🎮 CONTROL PANEL",
                         bg=COLORS['bg_dark'], fg=COLORS['accent_blue'],
                         font=('Segoe UI', 14, 'bold'), pady=10)
        header.pack(fill=tk.X)
        
        # Scrollable content
        canvas = tk.Canvas(panel, bg=COLORS['bg_medium'], highlightthickness=0)
        scrollbar = ttk.Scrollbar(panel, orient="vertical", command=canvas.yview)
        scrollable_frame = tk.Frame(canvas, bg=COLORS['bg_medium'])
        
        scrollable_frame.bind(
            "<Configure>",
            lambda e: canvas.configure(scrollregion=canvas.bbox("all"))
        )
        
        canvas.create_window((0, 0), window=scrollable_frame, anchor="nw")
        canvas.configure(yscrollcommand=scrollbar.set)
        
        # Controller selection
        self.create_section(scrollable_frame, "🎯 Controller Type")
        self.controller_type = tk.StringVar(value="PID")
        
        ctrl_frame = tk.Frame(scrollable_frame, bg=COLORS['bg_medium'])
        ctrl_frame.pack(fill=tk.X, padx=20, pady=5)
        
        tk.Radiobutton(ctrl_frame, text="Bang-Bang", variable=self.controller_type,
                      value="Bang-Bang", bg=COLORS['bg_medium'], fg=COLORS['text_light'],
                      selectcolor=COLORS['bg_dark'], font=('Segoe UI', 10),
                      command=self.on_controller_change).pack(anchor='w')
        tk.Radiobutton(ctrl_frame, text="PID", variable=self.controller_type,
                      value="PID", bg=COLORS['bg_medium'], fg=COLORS['text_light'],
                      selectcolor=COLORS['bg_dark'], font=('Segoe UI', 10),
                      command=self.on_controller_change).pack(anchor='w')
        
        # Rocket parameters
        self.create_section(scrollable_frame, "🚀 Rocket Parameters")
        self.param_vars = {}
        
        params = [
            ("Mass (kg)", 'm', 500, 2000, 1000),
            ("Gravity (m/s²)", 'g', 1, 20, 9.81),
            ("Max Thrust (N)", 'T_max', 5000, 30000, 15000),
            ("Initial Altitude (m)", 'z0', 100, 2000, 1000),
            ("Initial Velocity (m/s)", 'v0', -100, -10, -50),
            ("Target Velocity (m/s)", 'target_velocity', -5, -0.5, -2)
        ]
        
        for label, key, min_val, max_val, default in params:
            self.param_vars[key] = self.create_slider(
                scrollable_frame, label, min_val, max_val, default
            )
        
        # Controller parameters
        self.create_section(scrollable_frame, "⚙️ Controller Parameters")
        self.controller_vars = {}
        
        # Bang-Bang params
        self.bb_frame = tk.Frame(scrollable_frame, bg=COLORS['bg_medium'])
        self.controller_vars['threshold'] = self.create_slider(
            self.bb_frame, "Velocity Threshold (m/s)", -30, -2, -10
        )
        
        # PID params
        self.pid_frame = tk.Frame(scrollable_frame, bg=COLORS['bg_medium'])
        self.controller_vars['Kp'] = self.create_slider(
            self.pid_frame, "Kp (Proportional)", 0, 2000, 500
        )
        self.controller_vars['Ki'] = self.create_slider(
            self.pid_frame, "Ki (Integral)", 0, 200, 50
        )
        self.controller_vars['Kd'] = self.create_slider(
            self.pid_frame, "Kd (Derivative)", 0, 3000, 1000
        )
        
        # Action buttons
        self.create_section(scrollable_frame, "🎬 Actions")
        btn_frame = tk.Frame(scrollable_frame, bg=COLORS['bg_medium'])
        btn_frame.pack(fill=tk.X, padx=20, pady=10)
        
        self.run_btn = tk.Button(btn_frame, text="▶ RUN SIMULATION",
                                 command=self.run_simulation,
                                 bg=COLORS['accent_green'], fg=COLORS['text_light'],
                                 font=('Segoe UI', 11, 'bold'), relief=tk.FLAT,
                                 padx=20, pady=12, cursor='hand2')
        self.run_btn.pack(fill=tk.X, pady=5)
        
        tk.Button(btn_frame, text="🔄 RESET",
                 command=self.reset_simulation,
                 bg=COLORS['accent_orange'], fg=COLORS['text_light'],
                 font=('Segoe UI', 10, 'bold'), relief=tk.FLAT,
                 padx=20, pady=10, cursor='hand2').pack(fill=tk.X, pady=5)
        
        tk.Button(btn_frame, text="📊 COMPARE ALL",
                 command=self.compare_controllers,
                 bg=COLORS['accent_blue'], fg=COLORS['text_light'],
                 font=('Segoe UI', 10, 'bold'), relief=tk.FLAT,
                 padx=20, pady=10, cursor='hand2').pack(fill=tk.X, pady=5)
        
        # Results display
        self.create_section(scrollable_frame, "📈 Results")
        self.results_frame = tk.Frame(scrollable_frame, bg=COLORS['panel_bg'],
                                     relief=tk.SUNKEN, bd=2)
        self.results_frame.pack(fill=tk.X, padx=20, pady=5)
        
        self.results_text = tk.Text(self.results_frame, height=12, width=35,
                                   bg=COLORS['panel_bg'], fg=COLORS['text_light'],
                                   font=('Consolas', 9), relief=tk.FLAT,
                                   padx=10, pady=10)
        self.results_text.pack(fill=tk.BOTH, expand=True)
        self.update_results_display("No simulation run yet")
        
        canvas.pack(side="left", fill="both", expand=True)
        scrollbar.pack(side="right", fill="y")
        
        return panel
    
    def create_section(self, parent, title):
        """Create a section header"""
        frame = tk.Frame(parent, bg=COLORS['bg_dark'], height=2)
        frame.pack(fill=tk.X, padx=10, pady=(15, 5))
        
        label = tk.Label(parent, text=title,
                        bg=COLORS['bg_medium'], fg=COLORS['accent_blue'],
                        font=('Segoe UI', 11, 'bold'))
        label.pack(anchor='w', padx=20, pady=(5, 5))
    
    def create_slider(self, parent, label, min_val, max_val, default):
        """Create a labeled slider"""
        frame = tk.Frame(parent, bg=COLORS['bg_medium'])
        frame.pack(fill=tk.X, padx=20, pady=5)
        
        var = tk.DoubleVar(value=default)
        
        label_frame = tk.Frame(frame, bg=COLORS['bg_medium'])
        label_frame.pack(fill=tk.X)
        
        tk.Label(label_frame, text=label,
                bg=COLORS['bg_medium'], fg=COLORS['text_light'],
                font=('Segoe UI', 9)).pack(side='left')
        
        value_label = tk.Label(label_frame, text=f"{default:.2f}",
                              bg=COLORS['bg_medium'], fg=COLORS['accent_orange'],
                              font=('Segoe UI', 9, 'bold'))
        value_label.pack(side='right')
        
        slider = tk.Scale(frame, from_=min_val, to=max_val, resolution=(max_val-min_val)/100,
                         orient=tk.HORIZONTAL, variable=var,
                         bg=COLORS['bg_light'], fg=COLORS['text_light'],
                         troughcolor=COLORS['bg_dark'], highlightthickness=0,
                         sliderlength=20, width=15, relief=tk.FLAT,
                         command=lambda v: value_label.config(text=f"{float(v):.2f}"))
        slider.pack(fill=tk.X)
        
        return var
    
    def create_visualization_panel(self, parent):
        """Create visualization panel with plots"""
        panel = tk.Frame(parent, bg=COLORS['bg_medium'], relief=tk.RAISED, bd=2)
        
        # Header
        header = tk.Label(panel, text="📊 REAL-TIME VISUALIZATION",
                         bg=COLORS['bg_dark'], fg=COLORS['accent_blue'],
                         font=('Segoe UI', 14, 'bold'), pady=10)
        header.pack(fill=tk.X)
        
        # Status bar
        self.status_bar = tk.Label(panel, text="● Ready to simulate",
                                  bg=COLORS['bg_dark'], fg=COLORS['success'],
                                  font=('Segoe UI', 10), pady=5)
        self.status_bar.pack(fill=tk.X)
        
        # Plot container
        self.plot_container = tk.Frame(panel, bg=COLORS['bg_medium'])
        self.plot_container.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        return panel
    
    def create_plots(self):
        """Create matplotlib figures"""
        # Create figure with dark theme
        plt.style.use('dark_background')
        self.fig = Figure(figsize=(10, 8), facecolor=COLORS['bg_medium'])
        self.fig.suptitle('Rocket Landing Telemetry', 
                         fontsize=16, fontweight='bold', color=COLORS['accent_blue'])
        
        # Create subplots
        gs = self.fig.add_gridspec(3, 2, hspace=0.35, wspace=0.25,
                                  left=0.08, right=0.95, top=0.93, bottom=0.07)
        
        self.ax_altitude = self.fig.add_subplot(gs[0, 0])
        self.ax_velocity = self.fig.add_subplot(gs[0, 1])
        self.ax_thrust = self.fig.add_subplot(gs[1, :])
        self.ax_phase = self.fig.add_subplot(gs[2, 0])
        self.ax_rocket = self.fig.add_subplot(gs[2, 1])
        
        # Style axes
        for ax in [self.ax_altitude, self.ax_velocity, self.ax_thrust, self.ax_phase]:
            ax.set_facecolor(COLORS['bg_dark'])
            ax.grid(True, alpha=0.2, color=COLORS['grid'])
            ax.spines['top'].set_visible(False)
            ax.spines['right'].set_visible(False)
            ax.spines['left'].set_color(COLORS['text_dim'])
            ax.spines['bottom'].set_color(COLORS['text_dim'])
            ax.tick_params(colors=COLORS['text_dim'])
        
        self.ax_rocket.set_facecolor(COLORS['bg_dark'])
        self.ax_rocket.axis('off')
        
        # Set labels
        self.ax_altitude.set_xlabel('Time (s)', color=COLORS['text_light'])
        self.ax_altitude.set_ylabel('Altitude (m)', color=COLORS['text_light'])
        self.ax_altitude.set_title('Altitude Profile', color=COLORS['accent_blue'], fontweight='bold')
        
        self.ax_velocity.set_xlabel('Time (s)', color=COLORS['text_light'])
        self.ax_velocity.set_ylabel('Velocity (m/s)', color=COLORS['text_light'])
        self.ax_velocity.set_title('Velocity Profile', color=COLORS['accent_blue'], fontweight='bold')
        
        self.ax_thrust.set_xlabel('Time (s)', color=COLORS['text_light'])
        self.ax_thrust.set_ylabel('Thrust (kN)', color=COLORS['text_light'])
        self.ax_thrust.set_title('Thrust Control Signal', color=COLORS['accent_blue'], fontweight='bold')
        
        self.ax_phase.set_xlabel('Altitude (m)', color=COLORS['text_light'])
        self.ax_phase.set_ylabel('Velocity (m/s)', color=COLORS['text_light'])
        self.ax_phase.set_title('Phase Portrait', color=COLORS['accent_blue'], fontweight='bold')
        
        self.ax_rocket.set_title('Rocket Animation', color=COLORS['accent_blue'], fontweight='bold')
        
        # Embed in tkinter
        self.canvas = FigureCanvasTkAgg(self.fig, master=self.plot_container)
        self.canvas.draw()
        self.canvas.get_tk_widget().pack(fill=tk.BOTH, expand=True)
    
    def on_controller_change(self):
        """Handle controller type change"""
        controller = self.controller_type.get()
        
        # Hide all controller frames
        self.bb_frame.pack_forget()
        self.pid_frame.pack_forget()
        
        # Show relevant frame
        if controller == "Bang-Bang":
            self.bb_frame.pack(fill=tk.X, padx=20, pady=5)
        else:
            self.pid_frame.pack(fill=tk.X, padx=20, pady=5)
    
    def update_parameters(self):
        """Update rocket parameters from GUI"""
        self.params.m = self.param_vars['m'].get()
        self.params.g = self.param_vars['g'].get()
        self.params.T_max = self.param_vars['T_max'].get()
        self.params.z0 = self.param_vars['z0'].get()
        self.params.v0 = self.param_vars['v0'].get()
        self.params.target_velocity = self.param_vars['target_velocity'].get()
    
    def create_controller(self):
        """Create controller based on selection"""
        controller_type = self.controller_type.get()
        
        if controller_type == "Bang-Bang":
            threshold = self.controller_vars['threshold'].get()
            return BangBangController(self.params, threshold)
        else:
            Kp = self.controller_vars['Kp'].get()
            Ki = self.controller_vars['Ki'].get()
            Kd = self.controller_vars['Kd'].get()
            return PIDController(self.params, Kp, Ki, Kd)
    
    def run_simulation(self):
        """Run simulation in separate thread"""
        if self.is_simulating:
            messagebox.showwarning("Busy", "Simulation already running!")
            return
        
        self.update_parameters()
        self.current_controller = self.create_controller()
        self.simulator = RocketSimulator(self.params, self.current_controller)
        
        self.is_simulating = True
        self.run_btn.config(state='disabled', text="⏳ SIMULATING...")
        self.status_bar.config(text="● Simulating...", fg=COLORS['warning'])
        
        # Run in thread
        thread = threading.Thread(target=self._simulate_thread)
        thread.start()
    
    def _simulate_thread(self):
        """Simulation thread"""
        try:
            self.results = self.simulator.simulate(t_max=100.0, dt=0.05)
            self.root.after(0, self._simulation_complete)
        except Exception as e:
            self.root.after(0, lambda: self._simulation_error(str(e)))
    
    def _simulation_complete(self):
        """Handle simulation completion"""
        self.is_simulating = False
        self.run_btn.config(state='normal', text="▶ RUN SIMULATION")
        self.status_bar.config(text="● Simulation complete", fg=COLORS['success'])
        
        self.update_plots()
        self.update_results_display()
    
    def _simulation_error(self, error_msg):
        """Handle simulation error"""
        self.is_simulating = False
        self.run_btn.config(state='normal', text="▶ RUN SIMULATION")
        self.status_bar.config(text="● Error occurred", fg=COLORS['danger'])
        messagebox.showerror("Simulation Error", f"Error: {error_msg}")
    
    def update_plots(self):
        """Update all plots with simulation results"""
        if self.results is None:
            return
        
        # Clear axes
        self.ax_altitude.clear()
        self.ax_velocity.clear()
        self.ax_thrust.clear()
        self.ax_phase.clear()
        self.ax_rocket.clear()
        
        t = self.results['time']
        z = self.results['altitude']
        v = self.results['velocity']
        thrust = self.results['thrust']
        thrust_time = self.results['thrust_time']
        
        # Altitude plot
        self.ax_altitude.plot(t, z, color=COLORS['accent_blue'], linewidth=2, label='Altitude')
        self.ax_altitude.axhline(y=0, color=COLORS['danger'], linestyle='--', alpha=0.5)
        self.ax_altitude.fill_between(t, 0, z, alpha=0.2, color=COLORS['accent_blue'])
        self.ax_altitude.set_xlabel('Time (s)', color=COLORS['text_light'])
        self.ax_altitude.set_ylabel('Altitude (m)', color=COLORS['text_light'])
        self.ax_altitude.set_title('Altitude Profile', color=COLORS['accent_blue'], fontweight='bold')
        self.ax_altitude.grid(True, alpha=0.2, color=COLORS['grid'])
        
        # Velocity plot
        self.ax_velocity.plot(t, v, color=COLORS['accent_orange'], linewidth=2, label='Velocity')
        self.ax_velocity.axhline(y=0, color=COLORS['success'], linestyle='--', alpha=0.5)
        self.ax_velocity.axhline(y=self.params.target_velocity, color=COLORS['accent_green'], 
                                linestyle=':', alpha=0.7, label='Target')
        self.ax_velocity.set_xlabel('Time (s)', color=COLORS['text_light'])
        self.ax_velocity.set_ylabel('Velocity (m/s)', color=COLORS['text_light'])
        self.ax_velocity.set_title('Velocity Profile', color=COLORS['accent_blue'], fontweight='bold')
        self.ax_velocity.legend(loc='best', framealpha=0.8)
        self.ax_velocity.grid(True, alpha=0.2, color=COLORS['grid'])
        
        # Thrust plot
        if len(thrust) > 0:
            self.ax_thrust.plot(thrust_time, thrust/1000, color=COLORS['accent_green'], 
                              linewidth=2, label='Thrust')
            self.ax_thrust.fill_between(thrust_time, 0, thrust/1000, alpha=0.3, 
                                       color=COLORS['accent_green'])
        self.ax_thrust.set_xlabel('Time (s)', color=COLORS['text_light'])
        self.ax_thrust.set_ylabel('Thrust (kN)', color=COLORS['text_light'])
        self.ax_thrust.set_title('Thrust Control Signal', color=COLORS['accent_blue'], fontweight='bold')
        self.ax_thrust.grid(True, alpha=0.2, color=COLORS['grid'])
        
        # Phase portrait
        self.ax_phase.plot(z, v, color=COLORS['accent_blue'], linewidth=2, alpha=0.8)
        self.ax_phase.scatter([z[0]], [v[0]], color=COLORS['success'], s=150, 
                            marker='o', edgecolor='white', linewidth=2, zorder=5, label='Start')
        self.ax_phase.scatter([z[-1]], [v[-1]], color=COLORS['danger'], s=150, 
                            marker='X', edgecolor='white', linewidth=2, zorder=5, label='Land')
        self.ax_phase.axhline(y=0, color=COLORS['text_dim'], linestyle='--', alpha=0.5)
        self.ax_phase.axvline(x=0, color=COLORS['text_dim'], linestyle='--', alpha=0.5)
        self.ax_phase.set_xlabel('Altitude (m)', color=COLORS['text_light'])
        self.ax_phase.set_ylabel('Velocity (m/s)', color=COLORS['text_light'])
        self.ax_phase.set_title('Phase Portrait', color=COLORS['accent_blue'], fontweight='bold')
        self.ax_phase.legend(loc='best', framealpha=0.8)
        self.ax_phase.grid(True, alpha=0.2, color=COLORS['grid'])
        
        # Rocket animation
        self.draw_rocket_animation()
        
        self.canvas.draw()
    
    def draw_rocket_animation(self):
        """Draw rocket visualization"""
        if self.results is None:
            return
        
        z_final = self.results['altitude'][-1]
        v_final = self.results['velocity'][-1]
        success = self.results['success']
        
        self.ax_rocket.clear()
        self.ax_rocket.set_xlim(-1, 1)
        self.ax_rocket.set_ylim(-0.5, 2)
        self.ax_rocket.axis('off')
        
        # Draw ground
        self.ax_rocket.fill_between([-1, 1], -0.5, 0, color=COLORS['bg_light'], alpha=0.5)
        self.ax_rocket.plot([-1, 1], [0, 0], color=COLORS['text_light'], linewidth=3)
        
        # Calculate rocket position (normalized)
        max_height = self.params.z0
        rocket_y = 0.1 if abs(z_final) < 1 else 1.5
        
        # Draw rocket
        rocket_color = COLORS['success'] if success else COLORS['danger']
        
        # Rocket body
        body = plt.Rectangle((-0.1, rocket_y), 0.2, 0.4, 
                           facecolor=rocket_color, edgecolor='white', linewidth=2)
        self.ax_rocket.add_patch(body)
        
        # Rocket nose
        nose = plt.Polygon([(-0.1, rocket_y+0.4), (0, rocket_y+0.6), (0.1, rocket_y+0.4)],
                          facecolor=COLORS['accent_orange'], edgecolor='white', linewidth=2)
        self.ax_rocket.add_patch(nose)
        
        # Fins
        fin_left = plt.Polygon([(-0.1, rocket_y), (-0.2, rocket_y), (-0.1, rocket_y+0.1)],
                              facecolor=COLORS['accent_blue'], edgecolor='white', linewidth=1)
        fin_right = plt.Polygon([(0.1, rocket_y), (0.2, rocket_y), (0.1, rocket_y+0.1)],
                               facecolor=COLORS['accent_blue'], edgecolor='white', linewidth=1)
        self.ax_rocket.add_patch(fin_left)
        self.ax_rocket.add_patch(fin_right)
        
        # Thrust flame
        if abs(v_final) > 0.5 and rocket_y > 0.5:
            flame = plt.Polygon([(0, rocket_y), (-0.05, rocket_y-0.15), (0.05, rocket_y-0.15)],
                              facecolor=COLORS['accent_orange'], alpha=0.7)
            self.ax_rocket.add_patch(flame)
        
        # Status text
        status_text = "✓ LANDED" if success else "✗ CRASH"
        status_color = COLORS['success'] if success else COLORS['danger']
        
        self.ax_rocket.text(0, 1.8, status_text, ha='center', va='center',
                          fontsize=16, fontweight='bold', color=status_color)
        
        velocity_text = f"v = {v_final:.2f} m/s"
        self.ax_rocket.text(0, 1.6, velocity_text, ha='center', va='center',
                          fontsize=12, color=COLORS['text_light'])
    
    def update_results_display(self, message=None):
        """Update results text display"""
        self.results_text.config(state='normal')
        self.results_text.delete('1.0', tk.END)
        
        if message:
            self.results_text.insert('1.0', message)
        elif self.results:
            r = self.results
            text = f"""
╔══════════════════════════════════╗
║       LANDING RESULTS            ║
╚══════════════════════════════════╝

Controller: {self.controller_type.get()}

Landing Time:     {r['landing_time']:.2f} s
Landing Velocity: {r['landing_velocity']:.2f} m/s
Landing Altitude: {r['landing_altitude']:.2f} m
Fuel Used:        {r['fuel_used']:.0f} N·s

Success: {"✓ YES" if r['success'] else "✗ NO"}
"""
            
            if r['success']:
                if abs(r['landing_velocity']) < 2:
                    text += "\nRating: ⭐⭐⭐ EXCELLENT"
                elif abs(r['landing_velocity']) < 4:
                    text += "\nRating: ⭐⭐ GOOD"
                else:
                    text += "\nRating: ⭐ OK"
            else:
                text += "\nRating: ✗ FAILED"
            
            self.results_text.insert('1.0', text)
        
        self.results_text.config(state='disabled')
    
    def reset_simulation(self):
        """Reset simulation"""
        self.results = None
        self.update_results_display("Simulation reset")
        
        # Clear plots
        for ax in [self.ax_altitude, self.ax_velocity, self.ax_thrust, self.ax_phase]:
            ax.clear()
        self.ax_rocket.clear()
        self.canvas.draw()
        
        self.status_bar.config(text="● Ready to simulate", fg=COLORS['success'])
    
    def compare_controllers(self):
        """Compare all controller types"""
        messagebox.showinfo("Compare", 
                          "Comparison mode will run multiple simulations.\n"
                          "This may take a few moments...")
        
        # This would run multiple simulations and create comparison plots
        # For now, show a message
        messagebox.showinfo("Feature", 
                          "Multi-controller comparison will be displayed here.\n"
                          "Run individual simulations to compare manually.")


def main():
    """Launch the GUI application"""
    root = tk.Tk()
    app = RocketLandingGUI(root)
    
    # Center window
    root.update_idletasks()
    width = root.winfo_width()
    height = root.winfo_height()
    x = (root.winfo_screenwidth() // 2) - (width // 2)
    y = (root.winfo_screenheight() // 2) - (height // 2)
    root.geometry(f'{width}x{height}+{x}+{y}')
    
    root.mainloop()


if __name__ == "__main__":
    main()
