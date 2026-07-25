# JavaScript Complete Learning Guide for Full-Stack Development
**From Basics to Backend — Everything You Need to Build Modern Web Apps**

---

## Table of Contents
1. [Why JavaScript?](#why-javascript)
2. [Getting Started & Setup](#getting-started--setup)
3. [Core Language Basics](#core-language-basics)
4. [Functions, Scope & Closures](#functions-scope--closures)
5. [Objects, Prototypes & Classes](#objects-prototypes--classes)
6. [Asynchronous JavaScript](#asynchronous-javascript)
7. [Browser APIs & The DOM](#browser-apis--the-dom)
8. [Events & Forms](#events--forms)
9. [HTTP: Fetch, XHR & APIs](#http-fetch-xhr--apis)
10. [Modules, Bundlers & Tooling](#modules-bundlers--tooling)
11. [Node.js & Backend Basics](#nodejs--backend-basics)
12. [Databases & Persistence](#databases--persistence)
13. [Realtime: WebSockets](#realtime-websockets)
14. [Testing & Debugging](#testing--debugging)
15. [Security & Best Practices](#security--best-practices)
16. [Performance & Optimization](#performance--optimization)
17. [Common Patterns & Architectures](#common-patterns--architectures)
18. [Project Examples](#project-examples)
19. [Learning Path & Resources](#learning-path--resources)

---

## Why JavaScript?
- Runs in every browser: UI + behavior
- Runs on the server (Node.js): full-stack with one language
- Rich ecosystem: npm, libraries, frameworks (React, Vue, Svelte)
- Event-driven, asynchronous programming model — ideal for I/O heavy apps

---

## Getting Started & Setup
- Use your browser DevTools (F12) and the Console to run JS quick tests
- Create a simple HTML file and include a script:

```html
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>JS Test</title>
  </head>
  <body>
    <script src="main.js"></script>
  </body>
</html>
```

- Modern tooling (optional but recommended):
  - Node.js (LTS) + npm or Yarn
  - Editor/IDE: VS Code with ESLint, Prettier
  - Basic package.json: `npm init -y`

---

## Core Language Basics
### Values & Types
**Primitive types** are the basic building blocks of data in JavaScript. They are immutable (cannot be changed).

- **string**: Text data enclosed in quotes (`'`, `"`, or `` ` ``)
- **number**: Integers and decimals (JavaScript has only one number type, unlike other languages)
- **boolean**: true or false values (used for logic and conditions)
- **null**: Intentional absence of value (you explicitly set this)
- **undefined**: Variable declared but not assigned a value yet (JavaScript sets this automatically)
- **bigint**: For very large integers beyond Number.MAX_SAFE_INTEGER (add `n` suffix: `123n`)
- **symbol**: Unique identifier, used for object property keys (advanced use case)

**Objects** are collections of key/value pairs (properties). Everything that's not a primitive is an object (arrays, functions, dates, etc.)

```js
// STRINGS - text enclosed in quotes
let name = 'Alice';          // Single quotes
let city = "New York";       // Double quotes
let message = `Hello ${name}`; // Template literal (backticks) - allows embedded expressions

// NUMBERS - integers and decimals
let age = 30;                // Integer
let price = 19.99;           // Decimal
let negative = -5;           // Negative number
let infinity = Infinity;     // Special number value
let notANumber = NaN;        // "Not a Number" - result of invalid math operations

// BOOLEAN - true/false values
let active = true;           // Boolean true
let verified = false;        // Boolean false
let isAdult = age >= 18;     // Result of comparison is boolean

// NULL - intentional "no value"
let selectedUser = null;     // You set this to indicate "nothing selected"

// UNDEFINED - variable exists but has no value yet
let missing;                 // Declared but not assigned - automatically undefined
let result = undefined;      // Rarely set explicitly

// CHECKING TYPES with typeof operator
console.log(typeof name);    // "string"
console.log(typeof age);     // "number"
console.log(typeof active);  // "boolean"
console.log(typeof missing); // "undefined"
console.log(typeof null);    // "object" (historical bug in JavaScript!)
```

### Variables: var / let / const
**Variables** are containers that store values. JavaScript has three ways to declare them:

- **var**: Old way (ES5 and before)
  - Function-scoped (accessible throughout entire function)
  - Can be redeclared and updated
  - Hoisted to top of function (can use before declaration, but value is undefined)
  - **Avoid using var** - causes bugs with scope issues

- **let**: Modern way (ES6+) for changeable values
  - Block-scoped (only accessible within `{}` block)
  - Cannot be redeclared in same scope
  - Can be updated/reassigned
  - Not hoisted (cannot use before declaration)

- **const**: Modern way (ES6+) for constants
  - Block-scoped
  - Cannot be redeclared or reassigned
  - Must be initialized when declared
  - **Use const by default**, only use let when you need to reassign

```js
// VAR (old, avoid)
var oldWay = 'avoid this';
var oldWay = 'can redeclare'; // No error (bad!)

// LET - for values that change
let x = 1;
x = 2;              // ✅ Can reassign
let x = 3;          // ❌ Error: cannot redeclare in same scope

// CONST - for values that don't change
const PI = 3.1415;
PI = 3.14;          // ❌ Error: cannot reassign const
const USER_ID = 123;

// CONST with objects/arrays - the reference can't change, but contents can
const user = { name: 'Alice' };
user.name = 'Bob';  // ✅ Allowed - modifying property
user.age = 30;      // ✅ Allowed - adding property
user = {};          // ❌ Error - cannot reassign the whole object

const colors = ['red', 'blue'];
colors.push('green'); // ✅ Allowed - modifying array contents
colors = [];          // ❌ Error - cannot reassign the array

// BLOCK SCOPE demonstration
if (true) {
  let blockVar = 'only here';
  const blockConst = 'also only here';
}
console.log(blockVar); // ❌ Error: blockVar not defined outside block
```

### Operators
**Operators** perform operations on values (variables, literals).

**Arithmetic Operators** (math operations):
```js
let sum = 5 + 3;        // 8  - Addition
let diff = 5 - 3;       // 2  - Subtraction
let product = 5 * 3;    // 15 - Multiplication
let quotient = 15 / 3;  // 5  - Division
let remainder = 5 % 2;  // 1  - Modulo (remainder after division)
let power = 2 ** 3;     // 8  - Exponentiation (2 to the power of 3)

// Increment/Decrement
let count = 0;
count++;                // count = count + 1 (now 1)
count--;                // count = count - 1 (back to 0)

// Compound assignment
let x = 10;
x += 5;                 // x = x + 5 (now 15)
x -= 3;                 // x = x - 3 (now 12)
x *= 2;                 // x = x * 2 (now 24)
x /= 4;                 // x = x / 4 (now 6)
```

**Comparison Operators** (compare values, return boolean):
```js
// STRICT EQUALITY (checks value AND type) - ALWAYS USE THIS
5 === 5;                // true
5 === '5';              // false (number vs string)

// LOOSE EQUALITY (converts types, then compares) - AVOID THIS
5 == '5';               // true (string '5' converted to number)
0 == false;             // true (false converted to 0)

// NOT EQUAL
5 !== 3;                // true (strict not equal)
5 != '5';               // false (loose not equal)

// GREATER/LESS THAN
let age = 25;
age > 18;               // true
age >= 25;              // true (greater than or equal)
age < 30;               // true
age <= 25;              // true (less than or equal)
```

**Logical Operators** (combine boolean expressions):
```js
// AND (&&) - both must be true
if (age >= 18 && hasLicense) {
  console.log('Can drive');
}

// OR (||) - at least one must be true
if (isAdmin || isModerator) {
  console.log('Has special permissions');
}

// NOT (!) - inverts boolean
let isLoggedOut = !isLoggedIn;
if (!verified) {
  console.log('Please verify your email');
}

// SHORT-CIRCUIT EVALUATION
let user = getUser() || 'Guest';  // If getUser() returns null/undefined, use 'Guest'
user && user.login();              // Only call login if user exists
```

**Nullish Coalescing (??)** - returns right side only if left is null/undefined:
```js
let name = userName ?? 'Anonymous';  // Only use default if userName is null/undefined
let count = 0 ?? 10;                 // 0 (not null/undefined, so keeps 0)
```

**Optional Chaining (?.)** - safely access nested properties:
```js
let email = user?.profile?.email;    // Returns undefined if user or profile is null
let result = obj?.method?.();        // Safely call method if it exists
```

### Template Strings (Template Literals)
**Template strings** use backticks (`` ` ``) and allow:
- Multi-line strings without `\n`
- Embedded expressions with `${}`
- Cleaner string concatenation

```js
const name = 'Alice';
const age = 25;

// OLD WAY (string concatenation with +)
const oldGreet = 'Hello, ' + name + '! You are ' + age + ' years old.';

// NEW WAY (template literal)
const greet = `Hello, ${name}! You are ${age} years old.`;

// Multi-line strings
const html = `
  <div class="card">
    <h2>${name}</h2>
    <p>Age: ${age}</p>
  </div>
`;

// Expressions inside ${}
const message = `Next year you'll be ${age + 1} years old`;
const status = `User is ${age >= 18 ? 'adult' : 'minor'}`;
```

### Control Flow
**Control flow** determines which code runs based on conditions.

**if/else** - conditional execution:
```js
const age = 20;

// Simple if
if (age >= 18) {
  console.log('Adult');
}

// if...else
if (age >= 18) {
  console.log('Can vote');
} else {
  console.log('Too young to vote');
}

// if...else if...else (multiple conditions)
if (age < 13) {
  console.log('Child');
} else if (age < 18) {
  console.log('Teenager');
} else if (age < 65) {
  console.log('Adult');
} else {
  console.log('Senior');
}

// Ternary operator (shorthand for simple if/else)
const status = age >= 18 ? 'adult' : 'minor';
```

**switch** - choose one of many code blocks:
```js
const day = 'Monday';

switch (day) {
  case 'Monday':
    console.log('Start of work week');
    break;  // Exit switch (without break, falls through to next case)
  case 'Friday':
    console.log('Almost weekend!');
    break;
  case 'Saturday':
  case 'Sunday':
    console.log('Weekend!');
    break;
  default:  // Runs if no case matches
    console.log('Midweek');
}
```

**for loop** - repeat code a specific number of times:
```js
// Classic for loop: initialization; condition; increment
for (let i = 0; i < 5; i++) {
  console.log(i);  // Prints 0, 1, 2, 3, 4
}

// Loop through array by index
const colors = ['red', 'green', 'blue'];
for (let i = 0; i < colors.length; i++) {
  console.log(colors[i]);
}
```

**while loop** - repeat while condition is true:
```js
let count = 0;
while (count < 5) {
  console.log(count);
  count++;
}

// do...while - runs at least once
let num = 0;
do {
  console.log(num);
  num++;
} while (num < 5);
```

**for...of** - loop through array items (modern, preferred):
```js
const fruits = ['apple', 'banana', 'orange'];

// Get each item directly
for (const fruit of fruits) {
  console.log(fruit);  // apple, banana, orange
}

// Works with strings too
for (const char of 'Hello') {
  console.log(char);  // H, e, l, l, o
}
```

**for...in** - loop through object keys:
```js
const user = { name: 'Alice', age: 25, role: 'admin' };

for (const key in user) {
  console.log(`${key}: ${user[key]}`);
  // name: Alice
  // age: 25
  // role: admin
}
```

**break and continue**:
```js
// break - exit loop completely
for (let i = 0; i < 10; i++) {
  if (i === 5) break;  // Stop at 5
  console.log(i);      // 0, 1, 2, 3, 4
}

// continue - skip current iteration, continue with next
for (let i = 0; i < 5; i++) {
  if (i === 2) continue;  // Skip 2
  console.log(i);         // 0, 1, 3, 4
}
```

---

## Functions, Scope & Closures
### Function Declaration vs Expression
**Functions** are reusable blocks of code that perform a task. JavaScript has several ways to create functions.

**Function Declaration** (traditional way):
```js
// Hoisted - can be called before declaration in code
function add(a, b) {
  return a + b;  // return sends value back to caller
}

const result = add(5, 3);  // 8

// Functions without return statement return undefined
function greet(name) {
  console.log(`Hello, ${name}`);
  // No return - implicitly returns undefined
}
```

**Function Expression** (assigned to variable):
```js
// Not hoisted - must declare before use
const subtract = function(a, b) {
  return a - b;
};

const diff = subtract(10, 3);  // 7
```

**Arrow Functions** (ES6+, modern shorthand):
```js
// Basic syntax: (parameters) => { body }
const multiply = (a, b) => {
  return a * b;
};

// IMPLICIT RETURN (no curly braces, no return keyword)
const add2 = (a, b) => a + b;  // Automatically returns result

// Single parameter - parentheses optional
const square = x => x * x;

// No parameters - parentheses required
const getRandom = () => Math.random();

// Returning object literal - wrap in parentheses
const makeUser = (name, age) => ({ name, age });

// Arrow functions are SHORTER and don't have their own 'this'
// Use arrow functions for callbacks, array methods
// Use regular functions for object methods, constructors
```

**Key differences: Arrow vs Regular Functions**:
```js
// 1. Arrow functions don't have 'this' binding
const obj = {
  name: 'Alice',
  regular: function() {
    console.log(this.name);  // 'Alice' - this refers to obj
  },
  arrow: () => {
    console.log(this.name);  // undefined - this is from outer scope
  }
};

// 2. Arrow functions can't be constructors
const Person = (name) => { this.name = name; };
new Person('Bob');  // ❌ Error: Person is not a constructor

// 3. Arrow functions are more concise
const numbers = [1, 2, 3, 4];
// Regular function
const doubled1 = numbers.map(function(n) { return n * 2; });
// Arrow function (cleaner)
const doubled2 = numbers.map(n => n * 2);
```

### Default Parameters & Rest/Spread
**Default Parameters** - provide fallback values:
```js
// If argument not provided, use default value
function greet(name = 'friend', greeting = 'Hello') {
  return `${greeting}, ${name}!`;
}

greet();               // "Hello, friend!"
greet('Alice');        // "Hello, Alice!"
greet('Bob', 'Hi');    // "Hi, Bob!"

// Default can be expression
function createUser(name, id = Date.now()) {
  return { name, id };
}
```

**Rest Parameters (...)** - collect remaining arguments into array:
```js
// Accepts any number of arguments
function sum(...nums) {
  // nums is an array of all arguments passed
  return nums.reduce((total, n) => total + n, 0);
}

sum(1, 2);           // 3
sum(1, 2, 3, 4, 5);  // 15

// Rest must be last parameter
function log(level, ...messages) {
  console.log(`[${level}]`, ...messages);
}

log('ERROR', 'Failed to connect', 'Server timeout');
// [ERROR] Failed to connect Server timeout
```

**Spread Operator (...)** - expand array/object into individual elements:
```js
// ARRAYS
const arr1 = [1, 2, 3];
const arr2 = [4, 5];

// Combine arrays
const combined = [...arr1, ...arr2];  // [1, 2, 3, 4, 5]

// Copy array (shallow copy)
const copy = [...arr1];  // [1, 2, 3]

// Pass array as individual arguments
const numbers = [5, 12, 8, 1];
Math.max(...numbers);  // 12 (same as Math.max(5, 12, 8, 1))

// OBJECTS
const user = { name: 'Alice', age: 25 };
const updatedUser = { ...user, age: 26, city: 'NYC' };
// { name: 'Alice', age: 26, city: 'NYC' }

// Merge objects (later properties override earlier ones)
const defaults = { theme: 'light', lang: 'en' };
const userPrefs = { theme: 'dark' };
const settings = { ...defaults, ...userPrefs };
// { theme: 'dark', lang: 'en' }
```

### Scope
**Scope** determines where variables are accessible in your code.

**Global Scope** (accessible everywhere, but avoid overusing):
```js
// Variables declared outside any function/block
const APP_NAME = 'Career Hub';  // Global constant

function showApp() {
  console.log(APP_NAME);  // ✅ Can access global variable
}

// Problem: Easy to accidentally create globals
function bad() {
  globalVar = 'oops';  // ❌ No var/let/const = creates global (in non-strict mode)
}
```

**Function Scope** (variables inside function):
```js
function calculate() {
  const result = 10 + 5;  // Only accessible inside this function
  return result;
}

console.log(result);  // ❌ Error: result is not defined

// Each function has its own scope
function outer() {
  const x = 1;
  function inner() {
    const y = 2;
    console.log(x);  // ✅ Can access outer function's variables
  }
  console.log(y);  // ❌ Error: cannot access inner function's variables
}
```

**Block Scope** (let/const inside `{}`):
```js
if (true) {
  let blockVar = 'only in this block';
  const blockConst = 'also block-scoped';
  var oldWay = 'function-scoped, not block-scoped';
}

console.log(blockVar);  // ❌ Error: not accessible outside block
console.log(oldWay);    // ✅ Accessible (var ignores block scope)

// Loops create block scope
for (let i = 0; i < 3; i++) {
  // i is only accessible here
}
console.log(i);  // ❌ Error: i not defined
```

**Scope Chain** (inner can access outer):
```js
const global = 'global';

function outer() {
  const outerVar = 'outer';
  
  function inner() {
    const innerVar = 'inner';
    console.log(global);     // ✅ Access global
    console.log(outerVar);   // ✅ Access outer
    console.log(innerVar);   // ✅ Access own
  }
  
  inner();
  console.log(innerVar);  // ❌ Error: can't access inner's variables
}
```

### Closures
**Closure**: A function that "remembers" variables from its outer scope, even after outer function has returned.

**Why closures are useful**:
- Private variables (encapsulation)
- Factory functions
- Event handlers that need access to local data

```js
// BASIC CLOSURE EXAMPLE
function makeCounter() {
  let count = 0;  // Private variable
  
  // This returned function is a closure
  return function() {
    count++;  // Accesses outer function's variable
    return count;
  };
}

const counter1 = makeCounter();
const counter2 = makeCounter();  // Each counter has its own 'count'

console.log(counter1());  // 1
console.log(counter1());  // 2
console.log(counter2());  // 1 (separate count variable)

// count is private - cannot access directly
console.log(counter1.count);  // undefined
```

**Real-world closure examples**:
```js
// 1. PRIVATE STATE (like private variables in OOP)
function createBankAccount(initialBalance) {
  let balance = initialBalance;  // Private - cannot access from outside
  
  return {
    deposit(amount) {
      balance += amount;
      return balance;
    },
    withdraw(amount) {
      if (amount > balance) {
        return 'Insufficient funds';
      }
      balance -= amount;
      return balance;
    },
    getBalance() {
      return balance;
    }
  };
}

const account = createBankAccount(100);
account.deposit(50);        // 150
account.withdraw(30);       // 120
account.getBalance();       // 120
account.balance = 9999;     // ❌ Cannot directly modify balance

// 2. EVENT HANDLER with closure
function setupButton(buttonId, message) {
  const button = document.getElementById(buttonId);
  
  // Handler closes over 'message'
  button.addEventListener('click', function() {
    alert(message);  // Remembers 'message' even after setupButton returns
  });
}

setupButton('btn1', 'Hello');
setupButton('btn2', 'Goodbye');

// 3. FACTORY FUNCTION
function createMultiplier(factor) {
  // Returns function that remembers 'factor'
  return function(number) {
    return number * factor;
  };
}

const double = createMultiplier(2);
const triple = createMultiplier(3);

double(5);  // 10
triple(5);  // 15
```

**Common closure pitfall** (loop with var):
```js
// ❌ PROBLEM: All handlers share same 'i'
for (var i = 0; i < 3; i++) {
  setTimeout(function() {
    console.log(i);  // Prints 3, 3, 3 (not 0, 1, 2)
  }, 100);
}

// ✅ SOLUTION 1: Use 'let' (block scope)
for (let i = 0; i < 3; i++) {
  setTimeout(function() {
    console.log(i);  // Prints 0, 1, 2 (each iteration gets own 'i')
  }, 100);
}

// ✅ SOLUTION 2: Create closure with IIFE
for (var i = 0; i < 3; i++) {
  (function(index) {
    setTimeout(function() {
      console.log(index);  // Prints 0, 1, 2
    }, 100);
  })(i);
}
```

---

## Objects, Prototypes & Classes
### Plain Objects
**Objects** are collections of key-value pairs (properties and methods).

```js
// CREATE OBJECT (literal notation)
const user = {
  // Properties (data)
  name: 'Alice',
  email: 'a@example.com',
  age: 25,
  
  // Method (function as property)
  login() {
    console.log(`${this.name} logged in`);
  },
  
  // Method with arrow function (different 'this' behavior)
  logout: () => {
    console.log('Logged out');
  }
};

// OBJECT CONSTRUCTOR (alternative)
const person = new Object();
person.name = 'Bob';
person.age = 30;

// COMPUTED PROPERTY NAMES
const key = 'email';
const obj = {
  [key]: 'test@example.com',  // Property name from variable
  ['user_' + 123]: 'dynamic'
};
```

### Property Access
```js
const user = { name: 'Alice', email: 'a@example.com' };

// DOT NOTATION (most common)
console.log(user.name);     // 'Alice'
user.name = 'Bob';          // Update property
user.age = 25;              // Add new property

// BRACKET NOTATION (for dynamic keys, special characters)
console.log(user['email']); // 'a@example.com'

// Dynamic property access
const prop = 'name';
console.log(user[prop]);    // 'Alice'

// Property names with spaces/special characters
const data = {
  'first-name': 'John',
  'user id': 123
};
data['first-name'];         // Must use bracket notation

// DELETE PROPERTY
delete user.age;

// CHECK IF PROPERTY EXISTS
if ('email' in user) { }          // true
if (user.hasOwnProperty('name')) { }  // true (own property, not inherited)
```

### Object Methods
```js
const user = { name: 'Alice', age: 25, role: 'admin' };

// Object.keys() - returns array of property names
const keys = Object.keys(user);  // ['name', 'age', 'role']

// Object.values() - returns array of values
const values = Object.values(user);  // ['Alice', 25, 'admin']

// Object.entries() - returns array of [key, value] pairs
const entries = Object.entries(user);
// [['name', 'Alice'], ['age', 25], ['role', 'admin']]

// Loop through object
for (const [key, value] of Object.entries(user)) {
  console.log(`${key}: ${value}`);
}

// Object.assign() - copy/merge objects
const defaults = { theme: 'light', lang: 'en' };
const userPrefs = { theme: 'dark' };
const settings = Object.assign({}, defaults, userPrefs);
// { theme: 'dark', lang: 'en' }

// Spread operator (cleaner alternative to Object.assign)
const merged = { ...defaults, ...userPrefs };

// Object.freeze() - make object immutable
const config = Object.freeze({ apiUrl: 'https://api.example.com' });
config.apiUrl = 'changed';  // ❌ Silently fails (or throws error in strict mode)

// Object.seal() - prevent adding/removing properties (can still modify existing)
const sealed = Object.seal({ name: 'Alice' });
sealed.name = 'Bob';   // ✅ Allowed
sealed.age = 25;       // ❌ Not allowed
```

### Destructuring
**Extract properties from objects easily**:
```js
const user = { name: 'Alice', age: 25, email: 'a@example.com' };

// OLD WAY
const name = user.name;
const age = user.age;

// DESTRUCTURING (extract multiple properties at once)
const { name, age } = user;
console.log(name);  // 'Alice'
console.log(age);   // 25

// Rename variables
const { name: userName, age: userAge } = user;

// Default values (if property doesn't exist)
const { name, role = 'user' } = user;  // role defaults to 'user'

// Nested destructuring
const data = { user: { name: 'Alice', address: { city: 'NYC' } } };
const { user: { name, address: { city } } } = data;

// Function parameters
function greet({ name, age }) {
  return `Hello ${name}, age ${age}`;
}
greet(user);  // 'Hello Alice, age 25'

// Rest properties
const { name, ...rest } = user;  // rest = { age: 25, email: '...' }
```

### Prototypes & Inheritance
**Every object has a prototype** (hidden link to another object it inherits from).

```js
// All objects inherit from Object.prototype
const obj = {};
obj.toString();  // Inherited method from Object.prototype

// Arrays inherit from Array.prototype
const arr = [];
arr.push(1);  // Inherited method

// Check prototype
Object.getPrototypeOf(obj) === Object.prototype;  // true

// Constructor function (old way before classes)
function Person(name, age) {
  this.name = name;
  this.age = age;
}

Person.prototype.greet = function() {
  return `Hello, I'm ${this.name}`;
};

const person1 = new Person('Alice', 25);
person1.greet();  // 'Hello, I'm Alice'
```

### ES6 Classes (Modern Syntax)
**Classes** are syntactic sugar over prototypes - cleaner syntax for object-oriented programming.

```js
// BASIC CLASS
class User {
  // Constructor runs when creating new instance
  constructor(name, email) {
    this.name = name;    // Instance property
    this.email = email;
  }
  
  // Method (automatically added to prototype)
  greet() {
    return `Hello ${this.name}`;
  }
  
  // Getter (access like property)
  get displayName() {
    return this.name.toUpperCase();
  }
  
  // Setter (set like property)
  set displayName(value) {
    this.name = value.trim();
  }
  
  // Static method (called on class, not instance)
  static createGuest() {
    return new User('Guest', 'guest@example.com');
  }
}

// CREATE INSTANCE
const user = new User('Alice', 'alice@example.com');
user.greet();           // 'Hello Alice'
user.displayName;       // 'ALICE' (getter)
user.displayName = 'Bob';  // (setter)

// Static method
const guest = User.createGuest();

// INHERITANCE (extends)
class Admin extends User {
  constructor(name, email, permissions) {
    super(name, email);  // Call parent constructor
    this.permissions = permissions;
  }
  
  // Override parent method
  greet() {
    return `Admin ${this.name}`;
  }
  
  // New method
  deleteUser(userId) {
    console.log(`Admin ${this.name} deleted user ${userId}`);
  }
}

const admin = new Admin('Eve', 'eve@example.com', ['delete', 'edit']);
admin.greet();  // 'Admin Eve'
admin.deleteUser(123);
```

### Array Methods (Essential)
**Arrays** are special objects with numeric keys and built-in methods.

```js
const numbers = [1, 2, 3, 4, 5];

// MAP - transform each element (returns new array)
const doubled = numbers.map(n => n * 2);  // [2, 4, 6, 8, 10]

// FILTER - keep elements that pass test (returns new array)
const evens = numbers.filter(n => n % 2 === 0);  // [2, 4]

// REDUCE - combine all elements into single value
const sum = numbers.reduce((total, n) => total + n, 0);  // 15
// (total starts at 0, then adds each n)

// FOREACH - loop through (no return value, just side effects)
numbers.forEach(n => console.log(n));

// FIND - return first element that matches
const firstEven = numbers.find(n => n % 2 === 0);  // 2

// FINDINDEX - return index of first match
const index = numbers.findIndex(n => n > 3);  // 3 (index of 4)

// SOME - check if at least one element passes test
const hasEven = numbers.some(n => n % 2 === 0);  // true

// EVERY - check if all elements pass test
const allPositive = numbers.every(n => n > 0);  // true

// SORT - sort array (mutates original!)
const sorted = [3, 1, 4].sort((a, b) => a - b);  // [1, 3, 4]

// INCLUDES - check if array contains value
numbers.includes(3);  // true

// Real-world examples
const users = [
  { name: 'Alice', age: 25, role: 'admin' },
  { name: 'Bob', age: 30, role: 'user' },
  { name: 'Charlie', age: 35, role: 'user' }
];

// Get all names
const names = users.map(u => u.name);  // ['Alice', 'Bob', 'Charlie']

// Filter admins
const admins = users.filter(u => u.role === 'admin');

// Calculate total age
const totalAge = users.reduce((sum, u) => sum + u.age, 0);  // 90

// Find user by name
const bob = users.find(u => u.name === 'Bob');

// Check if any user is admin
const hasAdmin = users.some(u => u.role === 'admin');  // true
```

---

## Asynchronous JavaScript
**JavaScript is single-threaded** but handles asynchronous operations (network requests, timers, file I/O) without blocking.

**Why async matters**:
- Network requests take time (don't freeze the page while waiting)
- Multiple operations can happen "at the same time"
- Improves user experience (responsive UI)

### Event Loop (Brief)
JavaScript uses an **event loop**:
1. Synchronous code runs first
2. Async operations (timers, fetch) go to Web APIs
3. When done, callbacks go to queue
4. Event loop checks queue and runs callbacks when main thread is free

### Callbacks (Old Way)
**Callback**: Function passed as argument, called when operation completes.

```js
// setTimeout - run code after delay
setTimeout(() => {
  console.log('Runs after 1 second');
}, 1000);

console.log('Runs first');  // Logs immediately

// Callback pattern
function fetchUser(id, callback) {
  setTimeout(() => {
    const user = { id, name: 'Alice' };
    callback(user);  // Call callback when done
  }, 1000);
}

fetchUser(123, (user) => {
  console.log(user.name);  // 'Alice' after 1 second
});

// PROBLEM: Callback Hell (nested callbacks)
fetchUser(1, (user) => {
  fetchPosts(user.id, (posts) => {
    fetchComments(posts[0].id, (comments) => {
      // 😱 Too many nested callbacks = hard to read/maintain
    });
  });
});
```

### Promises (Modern Way)
**Promise**: Object representing eventual completion/failure of async operation.

**States**:
1. **Pending**: Initial state, operation in progress
2. **Fulfilled**: Operation completed successfully (resolved)
3. **Rejected**: Operation failed (rejected)

```js
// CREATE PROMISE
const promise = new Promise((resolve, reject) => {
  setTimeout(() => {
    const success = true;
    if (success) {
      resolve('Success!');  // Fulfill promise with value
    } else {
      reject('Error!');     // Reject promise with reason
    }
  }, 1000);
});

// CONSUME PROMISE with .then() and .catch()
promise
  .then(result => {
    console.log(result);  // 'Success!'
    return 'next value';  // Can chain more .then()
  })
  .then(value => {
    console.log(value);  // 'next value'
  })
  .catch(error => {
    console.error(error);  // Handles any error in chain
  })
  .finally(() => {
    console.log('Always runs');  // Cleanup, runs regardless of success/failure
  });

// REAL EXAMPLE: fetch API (returns Promise)
fetch('/api/users')
  .then(response => {
    if (!response.ok) throw new Error('Network error');
    return response.json();  // Also returns Promise
  })
  .then(data => {
    console.log(data);  // Array of users
  })
  .catch(error => {
    console.error('Failed:', error);
  });

// PROMISE METHODS
// Promise.all() - wait for all promises (parallel)
Promise.all([
  fetch('/api/users'),
  fetch('/api/posts'),
  fetch('/api/comments')
])
  .then(responses => Promise.all(responses.map(r => r.json())))
  .then(([users, posts, comments]) => {
    // All data loaded
  })
  .catch(error => {
    // If ANY promise fails, catch runs
  });

// Promise.race() - first promise to complete wins
Promise.race([
  fetch('/api/data'),
  new Promise((_, reject) => 
    setTimeout(() => reject('Timeout'), 5000)
  )
]);

// Promise.allSettled() - wait for all, get results of each
Promise.allSettled([promise1, promise2, promise3])
  .then(results => {
    // results = [{ status: 'fulfilled', value: ... }, { status: 'rejected', reason: ... }]
  });
```

### async/await (Best Way - Modern)
**async/await** makes async code look synchronous - easier to read and write.

- **async function**: Always returns a Promise
- **await**: Pauses execution until Promise resolves (only works inside async function)

```js
// BASIC SYNTAX
async function fetchData() {
  // await pauses here until fetch completes
  const response = await fetch('/api/data');
  const data = await response.json();
  return data;  // Automatically wrapped in Promise
}

// Calling async function returns Promise
fetchData().then(data => console.log(data));

// Or use await if calling from another async function
async function main() {
  const data = await fetchData();
  console.log(data);
}

// ERROR HANDLING with try/catch
async function loadUser() {
  try {
    const res = await fetch('/api/user');
    
    if (!res.ok) {
      throw new Error(`HTTP error: ${res.status}`);
    }
    
    const user = await res.json();
    return user;
    
  } catch (error) {
    console.error('Failed to load user:', error);
    throw error;  // Re-throw if you want caller to handle it
  }
}

// SEQUENTIAL vs PARALLEL
// ❌ SLOW: Sequential (one after another)
async function slow() {
  const user = await fetch('/api/user');      // Wait 1s
  const posts = await fetch('/api/posts');    // Then wait 1s (2s total)
  // Total: 2 seconds
}

// ✅ FAST: Parallel (both at same time)
async function fast() {
  const [userRes, postsRes] = await Promise.all([
    fetch('/api/user'),
    fetch('/api/posts')
  ]);
  const user = await userRes.json();
  const posts = await postsRes.json();
  // Total: 1 second (both fetch at same time)
}

// REAL-WORLD EXAMPLE: Login form
async function handleLogin(event) {
  event.preventDefault();
  
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  
  try {
    // Show loading state
    document.getElementById('btn').disabled = true;
    
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    const data = await response.json();
    localStorage.setItem('token', data.token);
    window.location.href = '/dashboard';
    
  } catch (error) {
    alert('Login failed: ' + error.message);
  } finally {
    // Always runs (even if error)
    document.getElementById('btn').disabled = false;
  }
}
```

### Error Handling Best Practices
```js
// ❌ BAD: Unhandled promise rejection
fetch('/api/data');  // If fails, error disappears

// ✅ GOOD: Always handle errors
fetch('/api/data')
  .then(res => res.json())
  .catch(err => console.error(err));

// ✅ GOOD: With async/await
async function loadData() {
  try {
    const res = await fetch('/api/data');
    return await res.json();
  } catch (error) {
    console.error('Error:', error);
    return null;  // Return fallback value
  }
}

// Multiple async operations
async function loadAll() {
  const [users, posts] = await Promise.all([
    fetch('/api/users').then(r => r.json()).catch(() => []),
    fetch('/api/posts').then(r => r.json()).catch(() => [])
  ]);
  // Even if one fails, other continues
}
```

### Common Async Patterns
```js
// RETRY LOGIC
async function fetchWithRetry(url, retries = 3) {
  for (let i = 0; i < retries; i++) {
    try {
      return await fetch(url);
    } catch (error) {
      if (i === retries - 1) throw error;  // Last retry failed
      await new Promise(resolve => setTimeout(resolve, 1000));  // Wait before retry
    }
  }
}

// TIMEOUT
function fetchWithTimeout(url, timeout = 5000) {
  return Promise.race([
    fetch(url),
    new Promise((_, reject) => 
      setTimeout(() => reject(new Error('Timeout')), timeout)
    )
  ]);
}

// DEBOUNCE (delay until user stops typing)
function debounce(func, delay) {
  let timeoutId;
  return function(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
}

const searchInput = document.getElementById('search');
searchInput.addEventListener('input', debounce(async (e) => {
  const results = await fetch(`/api/search?q=${e.target.value}`);
  // Only runs after user stops typing for 300ms
}, 300));
```

---

## Browser APIs & The DOM
### The DOM
- Document Object Model: HTML as a tree of nodes
- Select elements:
  - document.getElementById
  - document.querySelector / querySelectorAll

```js
const btn = document.querySelector('#submit');
const inputs = document.querySelectorAll('.form-input');
```

### Modify DOM
```js
const div = document.createElement('div');
div.textContent = 'Hello';
document.body.appendChild(div);

// Set attributes
div.setAttribute('data-id', '123');
```

### Class manipulation
```js
el.classList.add('hidden');
el.classList.remove('active');
el.classList.toggle('open');
```

### Traversal
- parentElement, children, nextElementSibling, closest(selector)

---

## Events & Forms
### Add/Remove Event Listeners
```js
btn.addEventListener('click', (e) => { e.preventDefault(); doSomething(); });
```

### Event object
- event.target, event.currentTarget
- preventDefault(), stopPropagation()

### Delegation
Attach a single listener to a parent and detect clicks on children for performance.

```js
list.addEventListener('click', (e)=>{
  const item = e.target.closest('.item');
  if (!item) return;
  // handle item click
});
```

### Forms & Validation
```js
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = new FormData(form);
  const json = Object.fromEntries(data.entries());
  await fetch('/api/login', { method: 'POST', body: JSON.stringify(json), headers: {'Content-Type':'application/json'} });
});
```

---

## HTTP: Fetch, XHR & APIs
### Fetch API (modern)
```js
const res = await fetch('/api/jobs');
if (!res.ok) throw new Error('Network error');
const data = await res.json();
```
- Pass headers, body, method: fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(obj) })

### Handling JSON & Errors
- Check `res.ok` and `res.status`
- Use try/catch for network or parsing errors

### AbortController
- Cancel slow requests
```js
const controller = new AbortController();
fetch(url, { signal: controller.signal });
controller.abort();
```

### REST vs GraphQL
- REST: resource-based endpoints
- GraphQL: single endpoint with flexible queries

---

## Modules, Bundlers & Tooling
### ES Modules (Browser & Node)
```js
// export
export function add(a,b){ return a+b; }
// default export
export default class App {}

// import
import { add } from './math.js';
import App from './app.js';
```

- Use `<script type="module" src="main.js"></script>` in HTML

### Bundlers
- Rollup, Webpack, Parcel, Vite — bundle code for production
- Transpile with Babel for older browser support

### Package Management
- npm or Yarn
- Add dependencies: `npm install package` or `npm i -D package` for dev deps
- package.json scripts: `start`, `build`, `test`

### Linting & Formatting
- ESLint for code quality
- Prettier for consistent formatting

---

## Node.js & Backend Basics
### Why Node.js?
- JavaScript runtime on server using V8
- Great for real-time apps and APIs

### Minimal Express Server
```js
// server.js
const express = require('express');
const app = express();
app.use(express.json());
app.get('/api/hello', (req,res)=>res.json({ msg: 'hello' }));
app.post('/api/login', (req,res)=>{ /* login */ });
app.listen(3000, ()=>console.log('listening'));
```

### Project Structure (simple)
```
project/
  package.json
  server.js
  src/
    controllers/
    models/
    routes/
    utils/
```

### Environment Variables
- Use `.env` and packages like `dotenv` for local development
- Never commit secrets to source control

---

## Databases & Persistence
### SQL vs NoSQL
- SQL: PostgreSQL, MySQL — structured, relational
- NoSQL: MongoDB — flexible documents

### Example with PostgreSQL (node-postgres)
```js
const { Pool } = require('pg');
const pool = new Pool({ connectionString: process.env.DATABASE_URL });
const res = await pool.query('SELECT * FROM users WHERE id=$1', [id]);
```

### ORMs
- Sequelize, TypeORM, Prisma — simplify DB interactions

---

## Realtime: WebSockets
- Use WebSocket for bidirectional real-time communication
- Browser API: `const socket = new WebSocket('ws://...');`
- Server: `ws` library or native WebSocket server in Node.js
- Handle reconnection, heartbeats, authentication tokens

Example client:
```js
const socket = new WebSocket('ws://localhost:8080');
socket.addEventListener('open', ()=> console.log('open'));
socket.addEventListener('message', (e)=> console.log('msg', e.data));
socket.send(JSON.stringify({ type:'hello' }));
```

---

## Testing & Debugging
### Debugging
- Use `console.log` for quick checks
- Use DevTools breakpoints, watch expressions
- Node: `node --inspect` and Chrome DevTools

### Unit & Integration Testing
- Jest (popular), Mocha + Chai
- Test async functions with `async/await`

```js
// Example Jest test
test('adds', ()=>{ expect(add(1,2)).toBe(3); });
```

### End-to-end (E2E)
- Playwright or Cypress for UI-level tests

---

## Security & Best Practices
- Never trust user input (validate + sanitize)
- Use HTTPS in production
- Protect against XSS: escape content, use textContent
- CSRF protection for forms (tokens)
- Use secure cookies (HttpOnly, Secure, SameSite)
- Rate-limit public endpoints
- Hash passwords with bcrypt or argon2
- Validate and sanitize uploaded files

---

## Performance & Optimization
- Minify and bundle for production
- Use caching (HTTP caching, CDN)
- Debounce/throttle expensive UI handlers
- Use pagination and limit DB queries
- Avoid unnecessary DOM reflows (batch DOM updates)

---

## Common Patterns & Architectures
- MVC (Model-View-Controller)
- RESTful APIs
- Layered services: controllers -> services -> data access
- Pub/Sub for decoupled event systems (e.g., Redis, message queues)
- Microservices for larger applications

---

## Project Examples
### Simple Login Flow (client-side)
```js
// Handle form submit
form.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const data = Object.fromEntries(new FormData(form));
  const res = await fetch('/api/login', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
  if (res.ok) {
    const json = await res.json();
    localStorage.setItem('token', json.token);
    window.location.href = '/dashboard';
  } else {
    // show error
  }
});
```

### Simple API (Express)
```js
// routes/users.js
const express = require('express');
const router = express.Router();
router.get('/', async (req,res)=>{ const users = await db.getUsers(); res.json(users); });
module.exports = router;
```

---

## Learning Path (Practical)
### Week 1-2: Core JavaScript
- Syntax, types, functions, scope
- Arrays, objects, basic DOM

### Week 3-4: Intermediate
- Asynchronous JS (Promises, async/await)
- Fetch API, basic REST calls
- Event handling and forms

### Week 5-6: Backend & Full-Stack
- Node.js basics, Express API
- Database CRUD (Postgres or MongoDB)
- Authentication (JWT or session-based)

### Week 7-8: Advanced
- Testing, debugging, performance
- WebSockets and realtime
- Tooling: bundlers, linting, CI

---

## Quick Reference Cheat Sheet
```js
// Async
async function f(){
  try { const r = await fetch('/api'); const j = await r.json(); } catch(e) { }
}

// Fetch POST
fetch('/api', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({a:1}) });

// DOM select
document.querySelector('#id');

// Event
el.addEventListener('click', (e)=>{});

// Local storage
localStorage.setItem('key','value'); localStorage.getItem('key');
```

---

## Tools & Resources
- MDN Web Docs: https://developer.mozilla.org/en-US/docs/Web/JavaScript
- You Don't Know JS (book series)
- JavaScript.info
- Node.js docs
- FreeCodeCamp, Frontend Masters, Egghead.io
- StackOverflow, GitHub for examples

---

## Final Tips
- Build small projects: todo app, auth flow, CRUD app
- Learn by modifying open-source projects and reading source
- Keep functions small and testable
- Prefer `async/await` over raw Promises for readability
- Use TypeScript later to add type safety for large apps

---

**You're ready to start building full-stack apps with JavaScript. Practice by implementing features in this repo (e.g., API endpoints, client fetches, WebSocket handlers).**
