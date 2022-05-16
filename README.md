# Varimax
    Varimax The Slim PHP Frameworks


<li><strong>Home</strong>: <a href="http://www.varimax.cn">http://www.varimax.cn</a>
<li><strong>Source</strong>: <a href="https://github.com/dcto/varimax">https://github.com/dcto/varimax</a>
<li><strong>Issues</strong>: <a href="https://github.com/dcto/varimax/issues">https://github.com/dcto/varimax/issues</a>
<li><strong>License</strong>: MIT
<li><strong>IRC</strong>: #varimax on freenode

___
 <a href="https://packagist.org/packages/varimax/varimax"><img src="https://img.shields.io/packagist/l/varimax/varimax" alt="License"></a> <img src="https://img.shields.io/packagist/php-v/varimax/varimax" alt="PHP version"> <a href="https://packagist.org/packages/varimax/varimax"><img src="https://img.shields.io/github/v/release/dcto/varimax" alt="Latest Stable Version"></a>  <a href="https://packagist.org/packages/varimax/varimax"><img src="https://img.shields.io/packagist/dt/varimax/varimax" alt="Total Downloads"></a>


### Develop environment

touch the .env file into the root directory

that's content sample like it's

```
ENV=dev
DEBUG=2
```

ENV will load config directory config {ENV}.name

about DEBUG option item 1 vs 2

select 1 will be output error message without code error detail

select 2 will be output detail code exception message to the client


### Router

the varimax define some default route rule

```
':*'    =>  ':.+',
':str'  =>  ':[\w-]+',
':int'  =>  ':[1-9]\d+',
':num'  =>  ':[0-9.-]+',
':any'  =>  ':[\w!@$^&+-=|]+',
':hex'  =>  ':[a-f0-9]+',
':hash' =>  ':[a-z0-9]+',
':uuid' =>  ':[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
```

#### Restful APIs Style
```
Method     |  Path                |  Action   |
------------------------------------------------
GET        |  /test               |  index    |
GET        |  /test/(:id)         |  select   |
POST       |  /test/create        |  create   |
PUT/PATCH  |  /test/update/(:id)  |  update   |
DELETE     |  /test/delete/(:id)  |  delete   |
```    

#### Router demo
```
//公共组
Router::group( ['id' => 'public', 'prefix' => '/', 'namespace' => 'App\Controller'], function () {    
    Router::any( '/test/(list:*)/(id:\d+)' )->call( 'Test@test' );
    Router::get( '/test/(shop:vip|user)' )->call( 'Test@shop' ); //only allow vip or user string
    Router::get( '/test/(shop:vip|user)/(id:|\d+)' )->call( 'Test@shop' );
    //注册
    Router::post( '/signup' )->call( 'User@register' );
    //登录
    Router::post( '/signin' )->call( 'User@login' );
    //登出
    Router::get( '/logout' )->call( 'User@logout' );

    //Restful CRUD
    Router::restful('/user')->call( 'User@restful');
} );

//验证组
Router::group( ['id' => 'permit', 'prefix' => '/', 'namespace' => 'App\Controller', 'call' => 'App\Controller\Access@auth'], function () {

} ); 
```


#### About Deverloper

>Name : D.c (陶之11)

>Emai: sdoz@live.com
