phptcgen
========

Automatic Test Case Generator for PHP

phptcgen

Have you ever been in a situation where coming up with all the test cases for a particular method, including integration tests, is a tiresome practice of digging through the dependency tree of code?

Let’s say you’re writing a method foo() that calls bar() that calls baz() that calls fiz() and it just so happens that fiz() sometimes throws an Exception. And unfortunately, neither bar() nor baz() handles it. So you should have added an integration test case for that for foo(), but alas, with all the stuff going on in the code, it was overlooked.

What if we can simplify the work of figuring out all the possible outcomes based on automatically digging through the code dependency hierarchy?

That’s what phptcgen does. It provides a skeleton of test cases based on analysis of special docblock tags you add to your code, and handles all the complexities of the hierarchy.

You can use the skeleton as a starting point for your tests, and toss out any that are irrelevant, but also be aware of any test cases that you might have missed have you searched the hierarchy manually. This is to save time and hopefully have a more robust application in the end.

function foo(Bar b)
{
    return $b->thing();
}

We specify the possible outcomes of calling $b->thing():

direct outcomes:
returns true or false
may throw an exception E1
inherited outcomes:
$c->one()
may throw an exception E2
$c->two()
may return a string or null
etc.

So we have an outcomes tree that ultimately defines a set of test cases.

We define a list of all the possible outcomes of a given “root” function. A “root” function contains only native PHP function calls.

/**
 * @tc_takes nothing
 * @tc_uses php/echo
 * @tc_may text.output
 */
function sayHello()
{
    echo “hello”;
}

We specify that this method uses “php/echo”.

{
  “resources”: [
    “php/echo”
  ]
}

The system will automatically check it’s database for outcomes. 

In this case, “echo” does not return any values or modifies any data. It does not throw an error. It does have a side-effect of direct output. This has implications if you’re working with header() without output buffering as any output may result in “headers already sent” errors.

{
  “resources”: [
    “php/echo”: {
      “resources”: [        
      ],
      “outcomes”: {
        “text.output”
      }
    }
  ],
}

So if you’re working with sayHello() in foo() you will want to avoid code such as:

/**
 * @tc_takes nothing
 * @tc_uses sayHello php/header
 * @tc_may nothing
public function foo()
{
    sayHello();
    header(“Some: header”);
}

When running the test case generator, it will knows that foo() uses sayHello (so any side-effect results of that should be part of the test collection) and foo() uses header() so it should also consider the side-effects of that call. We know header() can produce an error message, “Output already started”. That produces a built-in side-effect of, “error.output.already.started” as a test-case. Another test case is that sayHello has some text output. The “tc_may nothing” indicates that this function itself doesn’t produce any outcome directly, and all outcomes are tied to it’s “tc_uses” dependencies. This is where the generator shines, in that, the writer of this method no longer needs to keep track of the outcomes. They will be known/realized, once the test case generator is used.

/**
 * Case 1: Normal call (no input)
 * @group coverage
 */
public function testFooCase1()
{
    foo();
    // Add your assertions here
    $this->markTestIncomplete();
}

/** 
 * Case 2: Call foo (outcome: warning.output.already.started)
 * @group error
 */
public function testFooCase2()
{
    foo();
    // Add your assertions here
    $this->markTestIncomplete();
}

/** 
 * Case 3: Call foo (outcome: text.output)
 * @group integration
 */
public function testFooCase3()
{
    foo();
    // Add your assertions here
    $this->markTestIncomplete();
}

Notice that the test case generator only looks at the docblock tags you specified. These test cases are a template or starting point.

Inputs

Let’s say we have:

/**
 * @tc_takes x.and.y.as.int, x.only.as.int, x.only.as.non.int, x.as.int.y.as.non.int
 * @tc_uses nothing
 * @tc_may nothing
 */
function add($x, $y = 0)
{
    return $x + $y;
}

In this case, the generator will produce 4 direct tests. No integration tests are created, since it does not depend on anything that may have side-effects. It’s possible that the addition operator fails in some unusual ways (out of memory?). It’s up to the user to add that case to @tc_may if they want to consider that possibility.

Inherited Outcomes

The power of the system comes when the outcomes are inherited. Without specifying it directly in the docblock, if you have the case where:

foo() calls bar() calls baz() calls fiz() and fiz() has the possibility of throwing an NoConfigFileFound exception which neither bar() nor baz() attempts to catch to handle, then it will be up to foo() to handle it.

The generator will automatically create a test case for that scenario.

The developer may choose to ignore that test case, but it would be deliberate rather than forgetting about that dependency on accident and not being prepared.

And by letting the generator discover the dependency outcomes tree, it saves a lot of brain cycles for the developer to focus on the actual method at hand.

And the production of the tree and test cases may enlighten the developer to add some special handlers in their method, to avoid problems.

Installing the tool

Using composer

php composer.phar install

Running the tool

phptcgen -R ./ -ext .php,.phtml -o tests/

This will recursively search the current folder for any php or phtml file and put the test cases into tests/.



