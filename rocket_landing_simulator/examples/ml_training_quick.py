"""
ML Training Example - Quick Demo
Train a machine learning controller and compare with experts

This is a simplified version for quick testing.
"""

import sys
sys.path.append('..')

from rocket_ml_controller import *
import matplotlib.pyplot as plt

print("\n" + "="*70)
print("🚀 ROCKET LANDING ML CONTROLLER - QUICK TRAINING DEMO")
print("="*70)

# Step 1: Create rocket parameters
print("\n📋 Step 1: Setting up rocket parameters...")
params = RocketParameters(
    m=1000.0,        # 1000 kg
    g=9.81,          # Earth gravity
    T_max=15000.0,   # 15 kN max thrust
    z0=1000.0,       # 1 km initial altitude
    v0=-50.0,        # -50 m/s initial velocity (falling)
    target_velocity=-2.0  # -2 m/s target landing velocity
)

print(f"  Mass: {params.m} kg")
print(f"  Gravity: {params.g} m/s²")
print(f"  Max Thrust: {params.T_max} N")
print(f"  Initial: {params.z0} m altitude, {params.v0} m/s velocity")
print(f"  Target landing: {params.target_velocity} m/s")

# Step 2: Train ML controller (quick version)
print("\n🤖 Step 2: Training ML controller...")
print("  Using PID expert for demonstrations")
print("  Training set: 20 trajectories (quick demo)")
print("  Epochs: 100 (quick demo)")

ml_controller, model, loss_history = train_ml_controller(
    params,
    expert_type='pid',
    n_trajectories=20,  # Quick version: 20 trajectories
    epochs=100          # Quick version: 100 epochs
)

# Step 3: Save the model
model_file = 'ml_model_quick_demo.pkl'
model.save(model_file)

# Step 4: Evaluate all controllers
print("\n🎯 Step 3: Evaluating controllers...")

# Create all controllers
pid_controller = PIDController(params, Kp=600, Ki=80, Kd=1200)
bangbang_controller = BangBangController(params, threshold=-8.0)

# Evaluate each
print("\n  PID Controller:")
pid_results = evaluate_controller(params, pid_controller)

print("\n  Bang-Bang Controller:")
bb_results = evaluate_controller(params, bangbang_controller)

print("\n  ML Controller:")
ml_results = evaluate_controller(params, ml_controller)

# Step 5: Create comparison plot
print("\n📊 Step 4: Creating comparison plots...")

fig, axes = plt.subplots(2, 2, figsize=(14, 10))
fig.suptitle('Rocket Landing Controller Comparison - Quick Demo', fontsize=16, fontweight='bold')

controllers_data = [
    (pid_controller, pid_results, 'PID', 'blue'),
    (bangbang_controller, bb_results, 'Bang-Bang', 'red'),
    (ml_controller, ml_results, 'ML', 'green')
]

# Plot altitude
for controller, results, label, color in controllers_data:
    axes[0, 0].plot(results['t'], results['z'], label=label, linewidth=2, color=color)
axes[0, 0].set_xlabel('Time (s)', fontsize=11)
axes[0, 0].set_ylabel('Altitude (m)', fontsize=11)
axes[0, 0].set_title('Altitude vs Time', fontsize=12, fontweight='bold')
axes[0, 0].legend()
axes[0, 0].grid(True, alpha=0.3)

# Plot velocity
for controller, results, label, color in controllers_data:
    axes[0, 1].plot(results['t'], results['v'], label=label, linewidth=2, color=color)
axes[0, 1].axhline(y=params.target_velocity, color='black', linestyle='--', label='Target', alpha=0.5)
axes[0, 1].set_xlabel('Time (s)', fontsize=11)
axes[0, 1].set_ylabel('Velocity (m/s)', fontsize=11)
axes[0, 1].set_title('Velocity vs Time', fontsize=12, fontweight='bold')
axes[0, 1].legend()
axes[0, 1].grid(True, alpha=0.3)

# Plot thrust
for controller, results, label, color in controllers_data:
    thrust = [controller.compute_thrust(z, v, t) for t, z, v in zip(results['t'], results['z'], results['v'])]
    axes[1, 0].plot(results['t'], thrust, label=label, linewidth=2, color=color)
axes[1, 0].set_xlabel('Time (s)', fontsize=11)
axes[1, 0].set_ylabel('Thrust (N)', fontsize=11)
axes[1, 0].set_title('Thrust vs Time', fontsize=12, fontweight='bold')
axes[1, 0].legend()
axes[1, 0].grid(True, alpha=0.3)

# Performance comparison
labels_bar = ['PID', 'Bang-Bang', 'ML']
errors = [
    abs(pid_results['v_final'] - params.target_velocity),
    abs(bb_results['v_final'] - params.target_velocity),
    abs(ml_results['v_final'] - params.target_velocity)
]
colors_bar = ['blue', 'red', 'green']
bars = axes[1, 1].bar(labels_bar, errors, color=colors_bar, alpha=0.7)
axes[1, 1].set_ylabel('Landing Error (m/s)', fontsize=11)
axes[1, 1].set_title('Landing Precision Comparison', fontsize=12, fontweight='bold')
axes[1, 1].grid(True, alpha=0.3, axis='y')

# Add value labels on bars
for bar, error in zip(bars, errors):
    height = bar.get_height()
    axes[1, 1].text(bar.get_x() + bar.get_width()/2., height,
                    f'{error:.2f}',
                    ha='center', va='bottom', fontsize=10)

plt.tight_layout()
plot_file = 'ml_training_comparison_quick.png'
plt.savefig(plot_file, dpi=150, bbox_inches='tight')
print(f"  ✓ Plot saved: {plot_file}")
plt.show()

# Plot training loss
fig2, ax = plt.subplots(figsize=(10, 6))
ax.plot(loss_history, linewidth=2, color='green')
ax.set_xlabel('Epoch', fontsize=11)
ax.set_ylabel('Loss (MSE)', fontsize=11)
ax.set_title('ML Training Loss Over Time', fontsize=14, fontweight='bold')
ax.grid(True, alpha=0.3)
loss_file = 'ml_training_loss_quick.png'
plt.savefig(loss_file, dpi=150, bbox_inches='tight')
print(f"  ✓ Loss plot saved: {loss_file}")
plt.show()

# Summary
print("\n" + "="*70)
print("✅ TRAINING COMPLETE!")
print("="*70)

print("\n📊 Performance Summary:")
print(f"  PID:       Landing error = {errors[0]:.2f} m/s, Success = {'✓' if pid_results['success'] else '✗'}")
print(f"  Bang-Bang: Landing error = {errors[1]:.2f} m/s, Success = {'✓' if bb_results['success'] else '✗'}")
print(f"  ML:        Landing error = {errors[2]:.2f} m/s, Success = {'✓' if ml_results['success'] else '✗'}")

print(f"\n📁 Files created:")
print(f"  1. {model_file} - Trained ML model")
print(f"  2. {plot_file} - Comparison plots")
print(f"  3. {loss_file} - Training loss plot")

print("\n💡 Next Steps:")
print("  1. For better results, increase training:")
print("     - n_trajectories=50 (instead of 20)")
print("     - epochs=500 (instead of 100)")
print("  2. Try hyperparameter tuning")
print("  3. Experiment with different experts")
print("  4. Test on different planets (Moon, Mars)")

print("\n🚀 Happy landing!")
