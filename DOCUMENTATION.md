# CN | CN Editor - Documentation

## Usage(#usage)

### CN Editor Web App(#usage-editor)

#### Requirements(#usage-editor-requirements)
Since we are using some of Javascripts latest features, you need a modern up-to-date Browser to use the CN Editor App.  
We are working with the CN Editor using Google Chrome (v. 89 +) or Firefox (v. 88 +). Older versions and other browsers might work but we did not try ourselves.

#### Basic Functions(#usage-editor-basics)
We have done our best to make the editor as powerful and intuitive as possible. However, some features are certainly not self-explanatory. We will therefore provide a guide for use in the near future.

### API(#usage-api)
The API is already working but not completed, yet. We are currently implementing the [Nomisma Ontology](http://nomisma.org/ontology). We will update this documentation as soon as this feature is released.  
Check the list of [available entities](https://data.corpus-nummorum.eu/api/entities). There are also lists of [available coin parameters](http://jkdev:8107/api/parameters/coins) and [available type parameters](http://jkdev:8107/api/parameters/types). These parameters can be passed via GET or POST as single values or as an array of values.

### SPARQL(#usage-sparql)
We are providing a [SPARQL Endpoint](https://data.corpus-nummorum.eu/sparql). Some Frontend-features are currently not available, but the basic services are set. We are working on solving the remaining issues.

## Installation(#installation)

### Requirements(#installation-requirements)
* A Webserver with NGINX or Apache
* PHP 8.0 and the following extensions: php8.0-mysql, php8.0-fpm, php8.0-dom, php8.0-mbstring
* [PHP Composer](https://getcomposer.org/)
* A local or remote MySQL- or MariaDB-Server
* for active Development: [Yarn](https://yarnpkg.com/) or [NPM](https://www.npmjs.com/) - we further recommand [NVM](https://github.com/nvm-sh/nvm)

### Setup(#installation-setup)
* copy the files or clone the repository to your target location. We recommand to have a parent directory like `CN` and a subdirectory like `src` for the actual app files.
* create another subdirectory on the same level like `src` for the images and other static file stuff, e.g. `data` (if you have your data stored elsewhere create a symbolic link). Please note: your Server-User (`www-data`) needs writing permissions for this directory. Afterwards create a symbolic Link pointing from your `data`-subdirectory to `src/public/storage`
* go into the newly created src-directory and run `composer install` (ensure you are using the right PHP Version and have all required PHP Extensions installad)
* create an `.env`-file (you can use `.env.example` as a blueprint), enter the required data (DB Connection, Emailsettings etc.) There is a key called `APP_STORAGE`. If you created the data-Subdirectory as mentioned above you don't have to change the location. Otherwise you have to enter the path to your static data.
* grant writing permissions for www-data to `src/storage`
* Setup the databases using our SQL-Dumps in `src/sql` (Artisan Migrations are planed for the future). You need to create `cn_app` (must be set as default in .env) and `cn_data`. Please note: the Laravel-User must be the same for both databases. SELECT, UPDATE, INSERT and DELETE must be granted as permissions. More operational rights are not required and not recommanded.
* Setup the [Laravel Command Scheduler](https://laravel.com/docs/8.x/scheduling) as Cronjob: `* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`
* If your are in a local development instance you can run `php artisan serve` to start the Laravel Dev Server. For deployment you have to setup the server configuration (as described in the next section).
For further information concerning installation have a look at [Laravel's Installation tips](https://laravel.com/docs/8.x/installation)

#### NGINX Configuration(#installation-nginx)
We recommand to setup a NGINX Server Block. Create a new file in `/etc/nginx/sites-available` and paste in a code block like the one below:

```
server {

	# set Port to listen to
	listen 8000;
	listen [::]:8000;
	
	# set name of current Server
	server_name YOURSERVER;

	# set path to public directory in root directory
	root YOUR-ROOT-DIRECTORY/public;
	index index.php;

	charset utf-8;

	# Pass request to index
	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	# set path to PHP fpm.sock
	location ~ \.php$ {
		fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		include fastcgi_params;
	}
}
```

For further information have a look at [Laravel's Deployment tips](https://laravel.com/docs/8.x/deployment#nginx)

## Development(#dev)

### Database Structure(#dev-db)
We are running two databases at the moment. One is called `cn_data` containing all research datasets and the other one `cn_app` for all application management data (like users, settings etc.)  
`cn_data` is a typical relational database. The primary entities are coins (`data_coins`) and types (`data_types`). They are connected via tables having `_to_` in there name to secondary entities; e.G. `data_coins_to_mints` is pointing on `data_mints`. All IDs are called `id` and are integers only. Foreign Keys referencing to other CN-tables are named `id_entity`, e.G. `id_mint`, while IDs pointing to external platforms like nomisma are named something like `nomisma_id` in most case (please note: for easier maintenance, we try to avoid saving the baseURL of these platforms in our DB having the ID only. The baseURLs are stored in `config/dbi/url.php`). 

### Backend(#dev-backend)
The Backend is completely managed by Laravel and PHP. There is a public interface provided by `api` and an internal one called `dbi`.  
`api` is managed by `routes/api.php` and controlled by `App\Http\Controllers\dbi\APIController`.  
`dbi` is managed by `routes/web.php` and controlled by `App\Http\Controllers\dbi\dbiController`.  
These controllers handle the given parameters and the user authentication, but the actual request is passed to `App\Http\Controllers\dbi\dbiManager` in both cases. The `dbiManager` is the heart of the backend.

#### dbiManager(#dev-backend-dbimanager)
The `dbiManager` handles the requests coming from the api- or dbiController. Via `dbiProvider` and `dbiInterface` it is connected to all the scripts within the `App\Http\Controllers\dbi\entities` namespace. There is one seperate script for each available entity (an array in `config/dbi/permissions.php` controlls the access related to the access level of the current user). Most secondary entities can be handled without the need for further scripts. More complex entities like coins or types are split. They have their own namespace e.G. `App\Http\Controllers\dbi\entities\coins` and a script for each action. The `App\Http\Controllers\dbi\handler` namespace provides further assistance by providing a frame for these scripts.  
Some entities like the lists have their own namespace and interface to provide greater flexibility.

#### Artisan Commands(#dev-backend-commands)
`App\Console\Commands` provides some useful scripts which can be run by `php artisan SCRIPTNAME`. Most of them are automatic but of cause you can run them manually.

#### Authentification and User Management(#dev-backend-auth)
We are using [Laravel's Auth Mechanisms](https://laravel.com/docs/8.x/authentication#introduction) with some minor changes (e.G. the user table is called `app_editor_users` instead of the default name `users`).  
There is only one completly new feature: the access_level. Every newly registered user starts at level 1. This is not sufficient to use the CN Editor App. The new user must be assigned a higher level by an admin first (inside the CN Editor in the Administrator-Tab or directly within the users table in the DB). The higher the level, the more rights the user is granted. At the moment, this system is one-dimensional, which is sufficient for our purposes. We may have to expand this system in the future.

### Frontend(#dev-frontend)
The Frontend is a combination of PHP-Blades and Vue.js. 

#### AppController(#dev-appcontroller)
The `App\Http\Controllers\editor\AppController` is triggered by `routes/web.php` when calling the `/editor` URL. It checks the authentification of the user. If the user is at least Level 2 it queries the database for information about the user and passes them to the `resources/views/editor/app.blade.php`

#### app.blade.php(#dev-appblade)
The `resources/views/editor/app.blade.php` extends the `resources/views/editor/layout.blade.php`, receives the data from the controller and provides the template for the Vue.js-SPA

#### Vue.js SPA(#dev-vue-spa)
Everything beyond the `resources/views/editor/app.blade.php` is completely managed by Vue. Even the routing is no longer done by Laravel but by Vue Router.  

While developing the app you have to run `yarn run watch` (for productive deployment you need to run `yarn run production`) - otherwise your changes won't be visible since the vue markup has to be compiled into a javascript-file. Have a look at `webpack.mix.js` in the root directory to understand what the compiler is doing: it takes the `resources/js/editor/editor.js`, mixes in the required files (defined within the editor.js) and puts them into `public/js/editor.js`. It is than included in `resources/views/editor/layout.blade.php` as `<script src="{{ asset('js/editor.js') }}"></script>` within the html body.  

The `resources/js/editor/editor.js` is the root component of the Vue.js Single Page application. You will find its template block in `resources/views/editor/app.blade.php`.

You find all required Vue components in `resources/js/editor/`.    
`resources/js/editor/global` contains the core files like the `router.js`, the `Store.js` or the `handlers.js` which provides functions needed in serval places.  
`resources/js/editor/modules` and `resources/js/editor/templates` contain components and frames which are used in many other components, e.G. Buttons or the table laout for secondary entities.  
`resources/js/editor/pages` contains all entities. Its structure relates roughly to the router structure

#### Vuetify(#dev-vuetify)
The CN Editor App uses the Vuetify Material Design Framework. Check out the [Vuetify website](https://vuetifyjs.com/en/) if you want to know how to use these powerful components and the build-in CSS.