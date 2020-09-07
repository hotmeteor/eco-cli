# Contributing

Thanks for your interest in contributing to Eco.

## Local development

To set it up, start by cloning the repository on your system.

```sh
git clone https://github.com/hotmeteor/eco-cli.git eco-cli
cd eco-cli
```

Next, install the dependencies.

```sh
composer install
```

You can run any Eco command within the project folder:

```sh
php eco repo:current
```

It's often helpful to develop Eco within a real application. To do this, you'll need to link your repo to a local application via Composer, or manually add the link in your global Composer file.

Here are some resources for helping with that process:
- https://getcomposer.org/doc/05-repositories.md#path
- https://fetzi.dev/developing-composer-packages-locally/
- https://medium.com/pvtl/local-composer-package-development-47ac5c0e5bb4

Once this has been done you can run Eco without the `php` prefix: 

```sh
eco repo:current
```
