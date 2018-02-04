# Lattice
A lightweight PHP framework built using a pseudo-MVC pattern.


# Quick start

## Adding lattice to a project
1) Copy the files from `src` into the directory where you want to build the website.
2) Include the main file (`require "app/main.php";`) on any page where you wish to utilize the framework.


# Components

## Models
Models are PHP classes that represent specific objects in our code. They inherit an interface and common methods from an abstract `Model` class.

New models can be added to the `app/model` directory. By default, the lattice framework includes the following models:
```
app/model/image.php      Represents images and their basic attributes
app/model/model.php      Abstract class inherited by all models
app/model/session.php    Represents a user session
app/model/user.php       Represents a user account
```

### Using models
Models can be automatically included in scripts by prepending the name with a backslash `\`.

For example a model class called `something` could be included with: 
```php
$my_model = \something();
$my_model->name = "Something";
$my_model->save();
```

or, equivalently, by providing a data array to the constructor:
```php
$another_model = \something(["name" => "Something else"]);
$another_model->save();

```

### Model methods
Models inherit the following methods from the abstract class `model`.
```php
public function __construct( [$data] );
public function __get( $property );
public function __set( $property, $value );
public function jsonSerialize();

protected function __save( $columns, $values );
protected function create( $id, $variables, $values );
protected function update( $id, $variables, $values );
protected function delete();
protected function try_lazy_load( $property );
```

### Linking models with database tables
Models can be linked to a database table, which enables them to automatically create, update, and delete rows.

A database table can be specified by setting the protected `$table` variable. For example the following class would attempt to read and write to the table "something" during a save:
```php
<?php
namespace app\model;

class something extends model {
  protected static $table = "something";
  //...
}
?>
```

### Saving a model to the database (create or update)
Models have access to a protected method `_save($columns, $values)`.

By implementing a custom `save()` function, models can create or update rows in a database.

An example of a model with a custom save function might look like:
```php
<?php
namespace app\model;

class thing extends model {
  protected static $table = "things";
  public $id;
  public $name;  
  public $created_at;
  public $deleted_at;

  public function save(){
    self::__save(["name", "created_at", "deleted_at"],
                 [$this->name, $this->created_at, $this->deleted_at]);
  }
}
?>

```
This makes it possible to, for example, run the following code:
```php
$my_thing = \thing(["name" => "test"]);
$my_thing->save();
```
Please note that `$id` was not provided with the columns and values. This is the one and only variable that will always be automatically included for each model. For unsaved models, this will be `null`. Once a new model has been saved to the database, the `$id` property will be populated.

### Deleting a model from the database
Models are deleted by having their `deleted_at` property populated with a non-null value, and having this changed saved to the database.

The protected `delete()` method, inherited from the abstract `model` class, sets this property to the current time with `NOW()` automatically.


## Views
Views are HTML and PHP templates that can be combined to build complete pages.

New templates can be added under the `app/view` directory. The template files should end with `.tpl.php`.

Template files are designed to represent elements to be server-side-rendered using HTML and PHP.

A variable `$data` is included when parsing each template and can pass variables template.

For example, a template file `app/view/foo.tpl.php` may look like:
```
<div id="my_div">
  <p>This is some div.</p>
  <p>
    Data provided to the template: <?= $data->some_variable ?>
  </p>
</div>
```

Templates have access to all of the frameowrk's functions and can be nested within each other.

### Parsing a view
A template can be used by calling either `render($filename, [$variables]);` or `render_to_string($filename, [$variables])`.

`render()` immediately parses and prints the template to the output buffer.
`render_to_string()` parses the template, but returns the output as a string.

For example, a template file `app/view/foo.tpl.php` would be included by the following script:
```php
render("foo", ["some_variable" => "Hello world."]);

```

And template file `app/view/bar/baz.tpl.php` could be included by the following script, but saved as a string:
```php
$my_output = render_to_string("bar/baz", ["some_variable" => "Hello world."]);

```

