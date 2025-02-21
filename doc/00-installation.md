## Installation 

1. Until package symfony flex's recipe is not published in [recipes-contrib repository](https://github.com/symfony/recipes-contrib),
   you need to add following json to your project's `composer.json` file.

```json
"extra": {
    "symfony": {
        "endpoint": [
            "https://api.github.com/repos/pfaffkam/essa-recipes/contents/index.json",
            "flex://defaults"
        ]
    }
}
```

2. Install package via composer:
```bash
composer require pfaffkam/essa
```

3. Configure package.in `config/packages/essa.yaml`
