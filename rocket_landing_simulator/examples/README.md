# 🎯 Machine Learning Examples

Example scripts demonstrating ML rocket landing controller training and evaluation.

---

## 📁 Available Examples

### 1. `ml_training_quick.py` - Quick Training Demo

**Purpose**: Fast demonstration of complete ML training pipeline

**What it does**:
- Trains ML controller with PID expert
- Uses 20 trajectories, 100 epochs (~2 minutes)
- Compares PID, Bang-Bang, and ML controllers
- Generates comparison plots

**Usage**:
```bash
python ml_training_quick.py
```

**Output Files**:
- `ml_model_quick_demo.pkl` - Trained model
- `ml_training_comparison_quick.png` - 4-panel comparison
- `ml_training_loss_quick.png` - Training loss curve

**Expected Results**:
- Training completes in ~2 minutes
- Loss decreases from ~0.07 to ~0.01
- All controllers evaluated

---

## 🚀 Quick Start

1. **Navigate to examples folder**:
   ```bash
   cd rocket_landing_simulator/examples
   ```

2. **Run quick training demo**:
   ```bash
   python ml_training_quick.py
   ```

3. **Wait for training** (~2 minutes):
   - Generates 20 expert trajectories
   - Trains neural network (100 epochs)
   - Evaluates all controllers
   - Creates plots

4. **Check results**:
   - Review terminal output for performance
   - View generated plots
   - Load saved model for further use

---

## 📊 Example Workflow

```python
# Import from parent directory
import sys
sys.path.append('..')

from rocket_ml_controller import *

# 1. Setup parameters
params = RocketParameters(
    m=1000.0,
    g=9.81,
    T_max=15000.0,
    z0=1000.0,
    v0=-50.0,
    target_velocity=-2.0
)

# 2. Train controller
ml_controller, model, loss = train_ml_controller(
    params,
    expert_type='pid',
    n_trajectories=20,
    epochs=100
)

# 3. Evaluate
results = evaluate_controller(params, ml_controller)

# 4. Save model
model.save('my_model.pkl')

# 5. Later: Load and use
loaded_model = NeuralNetwork.load('my_model.pkl')
ml_controller = MLController(params, loaded_model)
```

---

## 💡 Tips

### Faster Training
```python
train_ml_controller(
    params,
    n_trajectories=10,  # Fewer trajectories
    epochs=50           # Fewer epochs
)
```

### Better Performance
```python
train_ml_controller(
    params,
    expert_type='bangbang',  # Better expert for this scenario
    n_trajectories=50,       # More data
    epochs=500               # Longer training
)
```

### Different Scenarios

**Moon Landing**:
```python
params = RocketParameters(
    m=1000.0,
    g=1.62,  # Moon gravity
    T_max=15000.0,
    z0=1000.0,
    v0=-50.0,
    target_velocity=-2.0
)
```

**Mars Landing**:
```python
params = RocketParameters(
    m=1000.0,
    g=3.71,  # Mars gravity
    T_max=15000.0,
    z0=1000.0,
    v0=-50.0,
    target_velocity=-2.0
)
```

**Heavy Rocket**:
```python
params = RocketParameters(
    m=3000.0,  # 3x heavier
    g=9.81,
    T_max=30000.0,  # 2x more thrust
    z0=1000.0,
    v0=-50.0,
    target_velocity=-2.0
)
```

---

## 📈 Understanding Results

### Training Loss
- **Good**: Loss decreases steadily
- **Bad**: Loss increases or oscillates wildly
- **Target**: Final loss < 0.01

### Landing Performance
- **Excellent**: Error < 1.0 m/s
- **Good**: Error < 3.0 m/s
- **Acceptable**: Error < 5.0 m/s
- **Needs Work**: Error > 5.0 m/s

### Success Criteria
Landing is successful if:
- Landing velocity within 1.0 m/s of target
- Rocket reaches ground (z = 0)
- Simulation completes without crash

---

## 🔧 Troubleshooting

### Problem: Training is slow
**Solution**: Reduce `n_trajectories` or `epochs`

### Problem: Poor landing performance
**Solutions**:
1. Use Bang-Bang expert instead of PID
2. Increase training data (n_trajectories=50)
3. Train longer (epochs=500)
4. Try different hyperparameters

### Problem: Import errors
**Solution**: Make sure to include:
```python
import sys
sys.path.append('..')
```

### Problem: Plots don't show
**Solution**: Matplotlib trying to show GUI. Close plot windows or use:
```python
plt.savefig('plot.png')  # Save without showing
# Remove plt.show()
```

---

## 📚 Additional Resources

- **Main documentation**: `../README.md`
- **ML Guide**: `../MACHINE_LEARNING_GUIDE.md` (if available)
- **Math guide**: `../DIFFERENTIAL_EQUATIONS_COMPLETE_GUIDE.md`
- **Testing report**: `../TESTING_REPORT.md`

---

## 🎯 Next Steps

After running the examples:

1. **Experiment with parameters**: Try different PID gains, thresholds
2. **Different planets**: Test on Moon, Mars
3. **Hyperparameter tuning**: Optimize learning rate, network architecture
4. **Transfer learning**: Train on Earth, test on Moon
5. **Custom scenarios**: Create your own challenges

---

**Happy Learning! 🚀**
