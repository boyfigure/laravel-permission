# Associate users with permissions and roles


### Sponsor

<table>
   <tr>
      <td><img src="http://offspring.github.io/laravel-permission/sponsor-logo.png"></td>
      <td>If you want to quickly add authentication and authorization to Laravel projects, feel free to check Auth0's Laravel SDK and free plan at <a href="https://auth0.com/overview?utm_source=GHsponsor&utm_medium=GHsponsor&utm_campaign=laravel-permission&utm_content=auth">https://auth0.com/overview</a>.</td>
   </tr>
</table>


[![Latest Version on Packagist](https://img.shields.io/packagist/v/offspring/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/offspring/laravel-permission)
[![Build Status](https://img.shields.io/travis/offspring/laravel-permission/master.svg?style=flat-square)](https://travis-ci.org/offspring/laravel-permission)
[![StyleCI](https://styleci.io/repos/42480275/shield)](https://styleci.io/repos/42480275)
[![Total Downloads](https://img.shields.io/packagist/dt/offspring/laravel-permission.svg?style=flat-square)](https://packagist.org/packages/offspring/laravel-permission)

This package allows you to manage user permissions and roles in a database.

Once installed you can do stuff like this:

```php
// Adding permissions to a user
$user->givePermissionTo('edit articles');

// Adding permissions via a role
$user->assignRole('writer');

$role->givePermissionTo('edit articles');
```

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/authorization), you can check if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

## Documentation, Installation, and Usage Instructions

See the [documentation site](https://docs.offspring.be/laravel-permission/v3/introduction/) for detailed installation and usage instructions.

## Need a UI?

The package doesn't come with any screens out of the box, you should build that yourself. Here are some options to get you started:

- [Laravel Nova package by @vyuldashev for managing Roles and Permissions](https://github.com/vyuldashev/nova-permission)

- [Laravel Nova package by @paras-malhotra for managing Roles and Permissions and permissions based authorization for Nova Resources](https://github.com/insenseanalytics/laravel-nova-permission)

- [Extensive tutorial for building permissions UI](https://scotch.io/tutorials/user-authorization-in-laravel-54-with-offspring-laravel-permission) by [Caleb Oki](http://www.caleboki.com/).

- [How to create a UI for managing the permissions and roles](http://www.qcode.in/easy-roles-and-permissions-in-laravel-5-4/)

- [Laravel User Management for managing users, roles, permissions, departments and authorization](https://github.com/Mekaeil/LaravelUserManagement) by [Mekaeil](https://github.com/Mekaeil)


### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email [freek@offspring.be](mailto:freek@offspring.be) instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Offspring, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://offspring.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

This package is heavily based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
on [permissions and roles](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16). His original code
can be found [in this repo on GitHub](https://github.com/laracasts/laravel-5-roles-and-permissions-demo).

Special thanks to [Alex Vanderbist](https://github.com/AlexVanderbist) who greatly helped with `v2`, and to [Chris Brown](https://github.com/drbyte) for his longtime support  helping us maintain the package.

## Alternatives

- [Povilas Korop](https://twitter.com/@povilaskorop) did an excellent job listing the alternatives [in an article on Laravel News](https://laravel-news.com/two-best-roles-permissions-packages). In that same article, he compares laravel-permission to [Joseph Silber](https://github.com/JosephSilber)'s [Bouncer]((https://github.com/JosephSilber/bouncer)), which in our book is also an excellent package.
- [ultraware/roles](https://github.com/ultraware/roles) takes a slightly different approach to its features.
- [santigarcor/laratrust](https://github.com/santigarcor/laratrust) implements team support
- [zizaco/entrust](https://github.com/zizaco/entrust) offers some wildcard pattern matching

## Support us

Offspring is a web design agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://offspring.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/offspring). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
