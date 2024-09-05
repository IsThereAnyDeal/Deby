# Deby

Simple PHP build and deploy tool.

## Capabilities

- upload to multiple hosts at once
- atomic releases and reverts
- fast deploys
- local and remote tasks
- target-dependent execution

## Examples

You can see basic example of config and simple build in `examples/` folder.

To run deby:
```php "bin/deby" --config "build/build.php" deploy@dev```

This would run `deploy` recipe for `dev` target with `build/build.php` config


## Glossary

### Host
Server where you want to deploy. 

### Target
A collection of hosts.

### Task
Single unit of work. Might be as simple or as complex as you need.

Task may be local or remote. Local tasks run once and don't require targets,
remote tasks require target and run for each host in the target.

### Recipe
A collection of tasks.