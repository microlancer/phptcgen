phptcgen
========

Automatic Dependency-Aware PHPUnit Test Case Generator

## What is phptcgen?

Have you ever been in a situation where coming up with all the test cases for a particular method, including integration tests, is a tiresome practice of digging through a deep dependency tree of code?

Let’s say you’re writing a method foo() that calls bar() that calls baz() that calls fiz() and it just so happens that fiz() sometimes throws an Exception. And unfortunately, neither bar() nor baz() handles it. So you should have added an integration test case for that for foo(), but alas, with all the stuff going on in the code, it was overlooked.

What if we can simplify the work of figuring out all the possible outcomes based on automatically digging through the code dependency hierarchy?

That’s what phptcgen does. It provides a skeleton of test cases based on analysis of special docblock tags you add to your code, and handles all the complexities of the hierarchy. You can toss out any that are irrelevant, but feel better that you didn't overlook some obscure test case.

## Installation

You can grab it using composer.

## How to Use

Note: phptcgen (in current form) requires all classes and tests to use namespaces and follow the ZF2 naming conventions

Add the following (all optional) annotations to your PHP method DocBlocks:

```php
class Foo
{
    /**
     * @tc_takes someInput1 someInput2 ...
     * @tc_uses \Some\Class::method1 \SomeClass::method2 ...
     * @tc_may worksWonderfully crashesHorribly
     */
    public function bar()
    {
        // ...
    }
}
```

The `@tc_takes` annotation specifies a list of input conditions that should be tested for the method. The format should be no-spaces camelcase. The name will be appended to the name of the test method, e.g. `testFooTakesSomeInput1` and `testFooTakesSomeInput2` would be generated for the above list.

The `@tc_uses` annotation specifies a list of dependencies in \Namespace\Class::method format. It is important to use a fully-qualified specification here, as it will search and match to your code. In case of a global namespace class, be sure to use a leading backslash (\\). This is a key piece to the automatic generator: based on the dependencies (and dependencies-of-dependencies recursively), *phptcgen* will generate all the test cases.

The `@tc_may` annotation specifies what could happen directly in this method call (you should not specify dependencies here, and let *phptcgen* handle that for you). The list can contain any result that a caller should care to test for. In the example above, the method `bar()` might `workWonderfully` or `crashHorribly`. The format should be no-spaces camelcase. The name will be appended to the name of test methods, e.g. `testBazWhenBarWorksWonderfully` and `testBazWhenBarCrashesHorribly`.

The magic happens when you have another class that depends on `Foo`.

```php
class Baz
{
    /**
     * @tc_uses \Foo::bar
     */
    public function baz()
    {
        // ... code that uses Foo::bar somewhere ...
    }
}
```

With only one small annotation `@tc_uses \Foo::bar`, *phptcgen* will automatically generate the following file:

```php
<?php

namespace Test;

class BazTest
{
    public function testBazWhenBarCrashesHorribly()
    {
        // ...
    }
    
    public function testBazWhenBarWorksWonderfully()
    {
        // ...
    }
}
```

And the magic of this is when there is a long ancestry of calls, *phptcgen* will create a nice, complete list of test cases without you having to worry about some unexpected result or digging through all the API docs. 

Run:

```
./bin/phptcgen my/source/code output/tests/
```

This will recursively search the folder `my/source/code` and place the test files into `output/tests`.

## Contributing

Please feel free to contribute! This is a tiny hobby project and any input/comments/pull-requests are appreciated!


