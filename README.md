# Text Me Class

## Installation

Use the package manager [composer](https://getcomposer.org/) to install Textme.

```bash
composer require textme/sms
```

## Usage

### Create a connection
```php
Textme\SMS::init($username, $password, $source = '(optinal)');
```


### Create a message
```php
Textme\SMS::addMessage($numbers, $message, $source = '(optinal)');
```
You must set a $source in init or in adding massage if its set in init its was be for all massages but in massage its only for this message.

* The $numbers can be many type of array or string:
```php
$number = '0500000000';
$numners = ['0500000000','0500000001','0500000002'];
$numbers = ['phone'=> '0500000000', 'id' => '1'];
$numbers = [
             ['phone'=> '0500000000', 'id' => '1'],
             ['phone'=> '0500000001', 'id' => '2'],
];
```
* The $message can type of array or string:
```php
$message = 'Your message here';
$message = [
            'template' => 'Your message here, yours {{var}} here',
            'var' => 'demo for var use',
];
```

### Create a messages

```php
Textme\SMS::addMessages($array);
```
* This $array must include keys numbers,message or its can be array inside array demo:
* The $message can type of array or string:
```php
$array = [
   'numbers' => '0500000000',
   'message' => 'test message',
];
$array  = [
             [
                'numbers' => '0500000000',
                'message' => 'test message',
             ],
             [
                'numbers' => '0500000001',
                'message' => 'test message 2',
             ],
];
```
Its work in same format like addMessage its work with templates for this need add key template to message and key for evry var.

### Get balance

For get the balance of your sms you can use:
```php
Textme\SMS::getBalance();
```
### Get response

For get the last response of system (send or balance):
```php
Textme\SMS::getResponse();
```
### Create object of class

You can create object of this class and use its all like object
```php
$sms = new Textme\SMS($username, $password, $settings = ['(optinal)']);
```

## License
[MIT](https://choosealicense.com/licenses/mit/)