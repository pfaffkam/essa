# Extensions
Some functionalities of library (to bring the functionalities out of the box) are implemented
as **extensions**.

## Extension configuration
When you're creating an extension, which requires some configuration - you should write *config* and *configurators*.

*Config* is a thing which should define YAML structure of extension configuration. 
It will be put automatically into `essa.yaml` file, as `extensions.NAME` yaml key, where name is the extension name.

*Configurator* is a class, which will be triggered when `essa:configure` command is called. 
It should allow idempotent interactive configure of extension.

