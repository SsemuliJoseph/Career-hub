"""
MACHINE LEARNING ROCKET LANDING CONTROLLER
Complete Neural Network Implementation from Scratch

Features:
- Custom neural network (no TensorFlow/PyTorch)
- Imitation learning from expert controllers
- Training pipeline with backpropagation
- Model persistence (save/load)
- Transfer learning support

Architecture: 4 → 64 → 32 → 16 → 1 (2,945 parameters)

Author: Career Hub Project
Date: December 4, 2025
"""

import numpy as np
import matplotlib.pyplot as plt
from scipy.integrate import solve_ivp
import pickle
from dataclasses import dataclass
from typing import List, Tuple, Optional
import warnings
warnings.filterwarnings('ignore')


# ============================================================================
# ROCKET PARAMETERS (Import from main simulator)
# ============================================================================

@dataclass
class RocketParameters:
    """Physical parameters of the rocket system"""
    m: float = 1000.0
    g: float = 9.81
    T_max: float = 15000.0
    z0: float = 1000.0
    v0: float = -50.0
    target_velocity: float = -2.0


# ============================================================================
# NEURAL NETWORK IMPLEMENTATION
# ============================================================================

class NeuralNetwork:
    """
    Feedforward Neural Network with backpropagation
    
    Architecture:
        Input (4) → Dense(64, ReLU) → Dense(32, ReLU) → Dense(16, ReLU) → Output(1, Sigmoid)
    
    Total Parameters: 2,945
    """
    
    def __init__(self, input_size: int = 4, hidden_sizes: List[int] = [64, 32, 16], output_size: int = 1):
        """
        Initialize neural network with He initialization
        
        Args:
            input_size: Number of input features
            hidden_sizes: List of hidden layer sizes
            output_size: Number of output neurons
        """
        self.input_size = input_size
        self.hidden_sizes = hidden_sizes
        self.output_size = output_size
        
        # Initialize weights and biases with He initialization
        self.weights = []
        self.biases = []
        
        layer_sizes = [input_size] + hidden_sizes + [output_size]
        
        for i in range(len(layer_sizes) - 1):
            # He initialization: mean=0, std=sqrt(2/n_in)
            W = np.random.randn(layer_sizes[i], layer_sizes[i+1]) * np.sqrt(2.0 / layer_sizes[i])
            b = np.zeros((1, layer_sizes[i+1]))
            
            self.weights.append(W)
            self.biases.append(b)
        
        # Store activations for backpropagation
        self.activations = []
        self.z_values = []
    
    def relu(self, x):
        """ReLU activation function"""
        return np.maximum(0, x)
    
    def relu_derivative(self, x):
        """Derivative of ReLU"""
        return (x > 0).astype(float)
    
    def sigmoid(self, x):
        """Sigmoid activation function"""
        return 1 / (1 + np.exp(-np.clip(x, -500, 500)))
    
    def sigmoid_derivative(self, x):
        """Derivative of sigmoid"""
        s = self.sigmoid(x)
        return s * (1 - s)
    
    def forward(self, X):
        """
        Forward propagation
        
        Args:
            X: Input array of shape (batch_size, input_size)
        
        Returns:
            Output predictions of shape (batch_size, output_size)
        """
        self.activations = [X]
        self.z_values = []
        
        # Hidden layers with ReLU
        for i in range(len(self.weights) - 1):
            z = np.dot(self.activations[-1], self.weights[i]) + self.biases[i]
            self.z_values.append(z)
            a = self.relu(z)
            self.activations.append(a)
        
        # Output layer with Sigmoid
        z_out = np.dot(self.activations[-1], self.weights[-1]) + self.biases[-1]
        self.z_values.append(z_out)
        y_pred = self.sigmoid(z_out)
        self.activations.append(y_pred)
        
        return y_pred
    
    def backward(self, X, y_true, learning_rate=0.001):
        """
        Backpropagation with gradient descent
        
        Args:
            X: Input array
            y_true: True output values
            learning_rate: Learning rate for weight updates
        """
        m = X.shape[0]  # Batch size
        
        # Output layer gradient (MSE loss derivative)
        dA = self.activations[-1] - y_true
        
        # Backpropagate through layers
        for i in reversed(range(len(self.weights))):
            # Compute gradients
            if i == len(self.weights) - 1:
                # Output layer (sigmoid)
                dZ = dA * self.sigmoid_derivative(self.z_values[i])
            else:
                # Hidden layers (ReLU)
                dZ = dA * self.relu_derivative(self.z_values[i])
            
            # Weight and bias gradients
            dW = np.dot(self.activations[i].T, dZ) / m
            db = np.sum(dZ, axis=0, keepdims=True) / m
            
            # Update weights and biases
            self.weights[i] -= learning_rate * dW
            self.biases[i] -= learning_rate * db
            
            # Propagate gradient to previous layer
            if i > 0:
                dA = np.dot(dZ, self.weights[i].T)
    
    def train(self, X_train, Y_train, epochs=500, learning_rate=0.001, batch_size=32, verbose=True):
        """
        Train the neural network
        
        Args:
            X_train: Training inputs (n_samples, input_size)
            Y_train: Training outputs (n_samples, output_size)
            epochs: Number of training epochs
            learning_rate: Learning rate
            batch_size: Mini-batch size
            verbose: Print progress
        
        Returns:
            loss_history: List of loss values per epoch
        """
        n_samples = X_train.shape[0]
        loss_history = []
        
        for epoch in range(epochs):
            # Shuffle data
            indices = np.random.permutation(n_samples)
            X_shuffled = X_train[indices]
            Y_shuffled = Y_train[indices]
            
            epoch_loss = 0
            n_batches = 0
            
            # Mini-batch training
            for i in range(0, n_samples, batch_size):
                X_batch = X_shuffled[i:i+batch_size]
                Y_batch = Y_shuffled[i:i+batch_size]
                
                # Forward pass
                Y_pred = self.forward(X_batch)
                
                # Compute loss (MSE)
                loss = np.mean((Y_pred - Y_batch)**2)
                epoch_loss += loss
                n_batches += 1
                
                # Backward pass
                self.backward(X_batch, Y_batch, learning_rate)
            
            avg_loss = epoch_loss / n_batches
            loss_history.append(avg_loss)
            
            if verbose and (epoch % 50 == 0 or epoch == epochs - 1):
                print(f"Epoch {epoch}/{epochs}, Loss: {avg_loss:.6f}")
        
        return loss_history
    
    def predict(self, X):
        """Make predictions"""
        return self.forward(X)
    
    def save(self, filename):
        """Save model to file"""
        model_data = {
            'input_size': self.input_size,
            'hidden_sizes': self.hidden_sizes,
            'output_size': self.output_size,
            'weights': self.weights,
            'biases': self.biases
        }
        with open(filename, 'wb') as f:
            pickle.dump(model_data, f)
        print(f"✓ Model saved to {filename}")
    
    @classmethod
    def load(cls, filename):
        """Load model from file"""
        with open(filename, 'rb') as f:
            model_data = pickle.load(f)
        
        model = cls(
            input_size=model_data['input_size'],
            hidden_sizes=model_data['hidden_sizes'],
            output_size=model_data['output_size']
        )
        model.weights = model_data['weights']
        model.biases = model_data['biases']
        
        print(f"✓ Model loaded from {filename}")
        return model


# ============================================================================
# ML CONTROLLER
# ============================================================================

class MLController:
    """Machine Learning Controller using trained neural network"""
    
    def __init__(self, params: RocketParameters, model: NeuralNetwork):
        self.params = params
        self.model = model
        self.t_start = 0
    
    def normalize_input(self, z, v, t, fuel_fraction=1.0):
        """
        Normalize inputs to [0, 1] range
        
        Args:
            z: Altitude (m)
            v: Velocity (m/s)
            t: Time (s)
            fuel_fraction: Remaining fuel fraction (0-1)
        
        Returns:
            Normalized input array
        """
        z_norm = z / self.params.z0
        v_norm = (v - self.params.v0) / (0 - self.params.v0)
        t_norm = min(t / 100.0, 1.0)
        
        return np.array([[z_norm, v_norm, t_norm, fuel_fraction]])
    
    def compute_thrust(self, z, v, t, fuel_fraction=1.0):
        """Compute thrust using neural network"""
        x_norm = self.normalize_input(z, v, t, fuel_fraction)
        thrust_normalized = self.model.predict(x_norm)[0, 0]
        
        # Convert from [0, 1] to [0, T_max]
        thrust = thrust_normalized * self.params.T_max
        
        return np.clip(thrust, 0, self.params.T_max)


# ============================================================================
# EXPERT CONTROLLERS FOR TRAINING DATA GENERATION
# ============================================================================

class PIDController:
    """PID Controller for generating expert demonstrations"""
    
    def __init__(self, params: RocketParameters, Kp=500, Ki=50, Kd=1000):
        self.params = params
        self.Kp = Kp
        self.Ki = Ki
        self.Kd = Kd
        self.integral = 0
        self.prev_error = 0
        self.t_prev = None
    
    def reset(self):
        """Reset controller state"""
        self.integral = 0
        self.prev_error = 0
        self.t_prev = None
    
    def compute_thrust(self, z, v, t):
        """Compute thrust using PID control on velocity"""
        if self.t_prev is None:
            self.t_prev = t
            dt = 0.01
        else:
            dt = max(t - self.t_prev, 1e-6)
            self.t_prev = t
        
        # Error in velocity
        error = v - self.params.target_velocity
        
        # PID terms
        self.integral += error * dt
        derivative = (error - self.prev_error) / dt
        
        # PID output
        thrust = self.Kp * error + self.Ki * self.integral + self.Kd * derivative
        
        self.prev_error = error
        
        return np.clip(thrust, 0, self.params.T_max)


class BangBangController:
    """Bang-Bang Controller for generating expert demonstrations"""
    
    def __init__(self, params: RocketParameters, threshold=-10.0):
        self.params = params
        self.threshold = threshold
    
    def compute_thrust(self, z, v, t):
        """Full thrust if falling too fast, zero otherwise"""
        if v < self.threshold:
            return self.params.T_max
        else:
            return 0.0


# ============================================================================
# TRAINING DATA GENERATION
# ============================================================================

def generate_training_data(params: RocketParameters, controller, n_trajectories=50, noise_level=0.1):
    """
    Generate training data from expert controller
    
    Args:
        params: Rocket parameters
        controller: Expert controller (PID or Bang-Bang)
        n_trajectories: Number of trajectories to generate
        noise_level: Add noise to parameters for diversity
    
    Returns:
        X: Input features (n_samples, 4)
        Y: Output thrust values (n_samples, 1)
    """
    print(f"\n🎯 Generating {n_trajectories} expert trajectories...")
    
    X_data = []
    Y_data = []
    
    for traj in range(n_trajectories):
        # Add noise to initial conditions for diversity
        z0 = params.z0 * (1 + np.random.uniform(-noise_level, noise_level))
        v0 = params.v0 * (1 + np.random.uniform(-noise_level, noise_level))
        
        # Reset controller if it has state
        if hasattr(controller, 'reset'):
            controller.reset()
        
        # Simulate trajectory
        def dynamics(t, state):
            z, v = state
            if z <= 0:
                return [0, 0]
            
            thrust = controller.compute_thrust(z, v, t)
            dz_dt = v
            dv_dt = -params.g + thrust / params.m
            
            return [dz_dt, dv_dt]
        
        # Event: detect ground contact
        def hit_ground(t, state):
            return state[0]
        hit_ground.terminal = True
        
        # Solve ODE
        sol = solve_ivp(
            dynamics,
            [0, 200],
            [z0, v0],
            method='RK45',
            events=hit_ground,
            dense_output=True,
            max_step=0.1
        )
        
        # Extract data points
        for i, t in enumerate(sol.t):
            z, v = sol.y[:, i]
            if z > 0:
                thrust = controller.compute_thrust(z, v, t)
                
                # Normalize inputs
                z_norm = z / params.z0
                v_norm = (v - params.v0) / (0 - params.v0)
                t_norm = min(t / 100.0, 1.0)
                fuel_frac = 1.0  # Simplified
                
                # Normalize output
                thrust_norm = thrust / params.T_max
                
                X_data.append([z_norm, v_norm, t_norm, fuel_frac])
                Y_data.append([thrust_norm])
        
        if (traj + 1) % 10 == 0:
            print(f"  Generated {traj + 1}/{n_trajectories} trajectories...")
    
    X = np.array(X_data)
    Y = np.array(Y_data)
    
    print(f"✓ Generated {len(X)} training samples")
    
    return X, Y


# ============================================================================
# TRAINING PIPELINE
# ============================================================================

def train_ml_controller(params: RocketParameters, expert_type='pid', n_trajectories=50, epochs=500):
    """
    Complete training pipeline
    
    Args:
        params: Rocket parameters
        expert_type: 'pid' or 'bangbang'
        n_trajectories: Number of expert trajectories
        epochs: Training epochs
    
    Returns:
        Trained MLController
    """
    print("\n" + "="*70)
    print("🤖 MACHINE LEARNING ROCKET LANDING CONTROLLER - TRAINING PIPELINE")
    print("="*70)
    
    # Step 1: Create expert controller
    if expert_type == 'pid':
        print("\n📊 Using PID expert controller")
        expert = PIDController(params)
    else:
        print("\n📊 Using Bang-Bang expert controller")
        expert = BangBangController(params)
    
    # Step 2: Generate training data
    X_train, Y_train = generate_training_data(params, expert, n_trajectories)
    
    # Step 3: Create and train neural network
    print(f"\n🧠 Training neural network (4 → 64 → 32 → 16 → 1)")
    print(f"   Total parameters: 2,945")
    print(f"   Epochs: {epochs}")
    print(f"   Learning rate: 0.001")
    print(f"   Batch size: 32")
    
    model = NeuralNetwork(input_size=4, hidden_sizes=[64, 32, 16], output_size=1)
    loss_history = model.train(X_train, Y_train, epochs=epochs, learning_rate=0.001, batch_size=32)
    
    # Step 4: Create ML controller
    ml_controller = MLController(params, model)
    
    print("\n✅ Training complete!")
    
    return ml_controller, model, loss_history


# ============================================================================
# EVALUATION
# ============================================================================

def evaluate_controller(params: RocketParameters, controller, verbose=True):
    """Evaluate a controller's performance"""
    
    def dynamics(t, state):
        z, v = state
        if z <= 0:
            return [0, 0]
        
        thrust = controller.compute_thrust(z, v, t)
        dz_dt = v
        dv_dt = -params.g + thrust / params.m
        
        return [dz_dt, dv_dt]
    
    def hit_ground(t, state):
        return state[0]
    hit_ground.terminal = True
    
    sol = solve_ivp(
        dynamics,
        [0, 200],
        [params.z0, params.v0],
        method='RK45',
        events=hit_ground,
        dense_output=True,
        max_step=0.1
    )
    
    # Results
    z_final = sol.y[0, -1]
    v_final = sol.y[1, -1]
    t_final = sol.t[-1]
    
    success = abs(v_final - params.target_velocity) < 1.0
    
    if verbose:
        print(f"  Landing velocity: {v_final:.2f} m/s (target: {params.target_velocity:.2f} m/s)")
        print(f"  Landing time: {t_final:.2f} s")
        print(f"  Success: {'✓' if success else '✗'}")
    
    return {
        'z': sol.y[0],
        'v': sol.y[1],
        't': sol.t,
        'v_final': v_final,
        't_final': t_final,
        'success': success
    }


# ============================================================================
# COMPARISON
# ============================================================================

def compare_controllers(params: RocketParameters, controllers, labels):
    """Compare multiple controllers visually"""
    
    fig, axes = plt.subplots(2, 2, figsize=(14, 10))
    fig.suptitle('Controller Comparison', fontsize=16, fontweight='bold')
    
    for controller, label in zip(controllers, labels):
        results = evaluate_controller(params, controller, verbose=False)
        
        # Plot trajectories
        axes[0, 0].plot(results['t'], results['z'], label=label, linewidth=2)
        axes[0, 1].plot(results['t'], results['v'], label=label, linewidth=2)
        
        # Plot thrust
        thrust = [controller.compute_thrust(z, v, t) for t, z, v in zip(results['t'], results['z'], results['v'])]
        axes[1, 0].plot(results['t'], thrust, label=label, linewidth=2)
        
        # Performance metrics
        axes[1, 1].bar(label, abs(results['v_final'] - params.target_velocity), alpha=0.7)
    
    axes[0, 0].set_xlabel('Time (s)')
    axes[0, 0].set_ylabel('Altitude (m)')
    axes[0, 0].set_title('Altitude vs Time')
    axes[0, 0].legend()
    axes[0, 0].grid(True, alpha=0.3)
    
    axes[0, 1].set_xlabel('Time (s)')
    axes[0, 1].set_ylabel('Velocity (m/s)')
    axes[0, 1].set_title('Velocity vs Time')
    axes[0, 1].legend()
    axes[0, 1].grid(True, alpha=0.3)
    
    axes[1, 0].set_xlabel('Time (s)')
    axes[1, 0].set_ylabel('Thrust (N)')
    axes[1, 0].set_title('Thrust vs Time')
    axes[1, 0].legend()
    axes[1, 0].grid(True, alpha=0.3)
    
    axes[1, 1].set_ylabel('Landing Error (m/s)')
    axes[1, 1].set_title('Landing Precision')
    axes[1, 1].grid(True, alpha=0.3, axis='y')
    
    plt.tight_layout()
    plt.show()


# ============================================================================
# MAIN DEMO
# ============================================================================

if __name__ == "__main__":
    print("\n" + "="*70)
    print("🚀 MACHINE LEARNING ROCKET LANDING CONTROLLER - DEMO")
    print("="*70)
    
    # Create rocket parameters
    params = RocketParameters(
        m=1000.0,
        g=9.81,
        T_max=15000.0,
        z0=1000.0,
        v0=-50.0,
        target_velocity=-2.0
    )
    
    print("\n📋 Rocket Parameters:")
    print(f"  Mass: {params.m} kg")
    print(f"  Gravity: {params.g} m/s²")
    print(f"  Max Thrust: {params.T_max} N")
    print(f"  Initial Altitude: {params.z0} m")
    print(f"  Initial Velocity: {params.v0} m/s")
    print(f"  Target Landing Velocity: {params.target_velocity} m/s")
    
    # Train ML controller
    ml_controller, model, loss_history = train_ml_controller(
        params,
        expert_type='pid',
        n_trajectories=50,
        epochs=500
    )
    
    # Save model
    model.save('ml_model_demo.pkl')
    
    # Evaluate
    print("\n🎯 Evaluating ML Controller:")
    ml_results = evaluate_controller(params, ml_controller)
    
    # Compare with experts
    print("\n📊 Comparing with Expert Controllers:")
    pid_expert = PIDController(params)
    bangbang_expert = BangBangController(params)
    
    compare_controllers(
        params,
        [pid_expert, bangbang_expert, ml_controller],
        ['PID', 'Bang-Bang', 'ML']
    )
    
    print("\n✅ Demo complete!")
