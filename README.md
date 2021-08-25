# Workflow

Steps in workflow are executed in the sequence as they are specified in the definition

Workflow defined like this:

```yaml
steps:
  - step1:
  - step2:
  - step3: 
```

will execute steps in following order `step1, step2, step3`

Step execution can be controlled with `next` argument on the step definition.

Workflow defined like this:

```yaml
steps:
  - step1:
      next: step3
  - step2:
      next: end
  - step3:
      next: step2
```

will execute steps in following order `step1, step3, step2`. It is important to note that in this case final step should
have `next` set to  `end` or have defined `return` variable.

Step can contain one of the following actions

- variable assigment
- conditional jump
- code execution

Variable assigment in steps.

```yaml
steps:
  - step1:
      assign:
        a: 1
        b: ${a + 1}
        c: "String with value"
        d: ${c . " " . (string)b}
```