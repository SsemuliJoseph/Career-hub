"""
Rocket Landing Test - Find Working Controller Parameters
"""

import sys
sys.path.append('..')

from rocket_ml_controller import *

print("\n🔧 Testing different PID parameters to find working configuration...\n")

params = RocketParameters(
    m=1000.0,
    g=9.81,
    T_max=15000.0,
    z0=1000.0,
    v0=-50.0,
    target_velocity=-2.0
)

# Test different PID configurations
test_configs = [
    {'Kp': 3000, 'Ki': 100, 'Kd': 5000, 'name': 'Aggressive'},
    {'Kp': 2000, 'Ki': 50, 'Kd': 3000, 'name': 'Balanced'},
    {'Kp': 1500, 'Ki': 30, 'Kd': 2000, 'name': 'Conservative'},
    {'Kp': 1000, 'Ki': 10, 'Kd': 1500, 'name': 'Gentle'},
]

print("="*70)
print("Testing PID Configurations")
print("="*70)

for config in test_configs:
    controller = PIDController(params, Kp=config['Kp'], Ki=config['Ki'], Kd=config['Kd'])
    
    print(f"\n{config['name']}: Kp={config['Kp']}, Ki={config['Ki']}, Kd={config['Kd']}")
    
    results = evaluate_controller(params, controller, verbose=True)
    
    error = abs(results['v_final'] - params.target_velocity)
    if error < 1.0:
        print(f"  ✓✓ EXCELLENT! Error: {error:.2f} m/s")
    elif error < 3.0:
        print(f"  ✓ GOOD! Error: {error:.2f} m/s")
    else:
        print(f"  ⚠ Needs improvement. Error: {error:.2f} m/s")

# Test Bang-Bang with different thresholds
print("\n\n" + "="*70)
print("Testing Bang-Bang Configurations")
print("="*70)

bb_thresholds = [-5.0, -7.5, -10.0, -12.5, -15.0]

for threshold in bb_thresholds:
    controller = BangBangController(params, threshold=threshold)
    
    print(f"\nThreshold: {threshold} m/s")
    
    results = evaluate_controller(params, controller, verbose=True)
    
    error = abs(results['v_final'] - params.target_velocity)
    if error < 1.0:
        print(f"  ✓✓ EXCELLENT! Error: {error:.2f} m/s")
    elif error < 3.0:
        print(f"  ✓ GOOD! Error: {error:.2f} m/s")
    else:
        print(f"  ⚠ Needs improvement. Error: {error:.2f} m/s")

print("\n" + "="*70)
print("✅ Test Complete!")
print("="*70)
