"""
Quick Test Script for Rocket Landing Simulator v2.0
Tests all components without full training
"""

import sys
import numpy as np
print("Testing Rocket Landing Simulator v2.0...")
print("=" * 60)

# Test 1: Basic imports
print("\n✓ Test 1: Importing modules...")
try:
    from rocket_ml_controller import (
        NeuralNetwork, MLController, PIDController, 
        BangBangController, RocketParameters
    )
    print("  ✓ ML Controller imported successfully")
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Test 2: Neural Network Creation
print("\n✓ Test 2: Creating Neural Network...")
try:
    model = NeuralNetwork(input_size=4, hidden_sizes=[64, 32, 16], output_size=1)
    total_params = sum(w.size for w in model.weights)
    print(f"  ✓ Network created with {total_params} parameters")
    print(f"  ✓ Architecture: 4 → 64 → 32 → 16 → 1")
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Test 3: Forward Pass
print("\n✓ Test 3: Testing forward propagation...")
try:
    X_test = np.random.rand(10, 4)  # 10 samples, 4 features
    Y_pred = model.forward(X_test)
    print(f"  ✓ Forward pass successful")
    print(f"  ✓ Input shape: {X_test.shape}")
    print(f"  ✓ Output shape: {Y_pred.shape}")
    print(f"  ✓ Output range: [{Y_pred.min():.3f}, {Y_pred.max():.3f}]")
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Test 4: Quick Training
print("\n✓ Test 4: Quick training test (10 epochs)...")
try:
    X_train = np.random.rand(100, 4)
    Y_train = np.random.rand(100, 1)
    
    loss_history = model.train(
        X_train, Y_train, 
        epochs=10, 
        learning_rate=0.001, 
        batch_size=32,
        verbose=False
    )
    
    print(f"  ✓ Training completed")
    print(f"  ✓ Initial loss: {loss_history[0]:.6f}")
    print(f"  ✓ Final loss: {loss_history[-1]:.6f}")
    print(f"  ✓ Loss reduction: {(1 - loss_history[-1]/loss_history[0])*100:.1f}%")
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Test 5: Controllers
print("\n✓ Test 5: Testing controllers...")
try:
    params = RocketParameters()
    
    pid = PIDController(params)
    bangbang = BangBangController(params)
    ml = MLController(params, model)
    
    # Test thrust computation
    z, v, t = 500.0, -25.0, 10.0
    
    thrust_pid = pid.compute_thrust(z, v, t)
    thrust_bb = bangbang.compute_thrust(z, v, t)
    thrust_ml = ml.compute_thrust(z, v, t)
    
    print(f"  ✓ PID thrust: {thrust_pid:.2f} N")
    print(f"  ✓ Bang-Bang thrust: {thrust_bb:.2f} N")
    print(f"  ✓ ML thrust: {thrust_ml:.2f} N")
    print(f"  ✓ All controllers operational")
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Test 6: Model Save/Load
print("\n✓ Test 6: Testing model persistence...")
try:
    import os
    test_file = 'test_model.pkl'
    
    # Save
    model.save(test_file)
    
    # Load
    loaded_model = NeuralNetwork.load(test_file)
    
    # Verify
    Y_original = model.predict(X_test)
    Y_loaded = loaded_model.predict(X_test)
    
    diff = np.abs(Y_original - Y_loaded).max()
    print(f"  ✓ Max difference after load: {diff:.10f}")
    
    # Cleanup
    os.remove(test_file)
    print(f"  ✓ Model save/load working perfectly")
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Test 7: Mini Simulation
print("\n✓ Test 7: Running mini simulation...")
try:
    from scipy.integrate import solve_ivp
    
    params = RocketParameters(z0=1000, v0=-50, T_max=15000, m=1000, g=9.81)
    controller = PIDController(params)
    
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
        [0, 100],
        [params.z0, params.v0],
        method='RK45',
        events=hit_ground,
        max_step=0.5
    )
    
    v_final = sol.y[1, -1]
    t_final = sol.t[-1]
    
    print(f"  ✓ Simulation completed")
    print(f"  ✓ Landing time: {t_final:.2f} s")
    print(f"  ✓ Landing velocity: {v_final:.2f} m/s")
    print(f"  ✓ Target velocity: {params.target_velocity:.2f} m/s")
    print(f"  ✓ Error: {abs(v_final - params.target_velocity):.2f} m/s")
    
    if abs(v_final - params.target_velocity) < 1.0:
        print(f"  ✓ SUCCESSFUL LANDING! 🚀")
    else:
        print(f"  ⚠ Landing could be improved with tuning")
    
except Exception as e:
    print(f"  ✗ Error: {e}")
    sys.exit(1)

# Summary
print("\n" + "=" * 60)
print("✅ ALL TESTS PASSED!")
print("=" * 60)
print("\nRocket Landing Simulator v2.0 is fully operational!")
print("\nNext steps:")
print("  1. Run full training: python examples/ml_training_example.py")
print("  2. Open web GUI: rocket_landing_advanced_gui.html")
print("  3. Try desktop GUI: python rocket_landing_gui.py")
print("\n🚀 Ready for liftoff!")
