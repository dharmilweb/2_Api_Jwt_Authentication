<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Api Authentication

Laravel is a provide default Api Jwt Authentication follow some command & stapes.
Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Api Authentication
- Create Project
    - Command :-
    ```
        laravel new 2_Api_Jwt_Authentication
        cd 2_Api_Jwt_Authentication
    ```

- Add Database :-

    - Change .env file :-
        ```html
            DB_CONNECTION=mysql
            DB_HOST=127.0.0.1
            DB_PORT=3306
            DB_DATABASE=1_Authenticaion
            DB_USERNAME=root
            DB_PASSWORD=
        ```

-  Add Tables in Database...
    - Command :-
        ```
            php artisan migrate
        ```

        <p align="center"><a href="https://raw.githubusercontent.com/dharmilweb/2_Api_Jwt_Authentication/main/public/Api_Auth/Input_0.png" target="_blank"><img src="https://github.com/dharmilweb/2_Api_Jwt_Authentication/blob/main/public/Api_Auth/Input_0.png" width="400" alt="Laravel Logo"></a></p>

- Add JWT Configration...
    - Command :-
        ```
            composer require tymon/jwt-auth
            php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
            php artisan jwt:secret
        ```

    - Add config/auth.php Page...
        ```html
            'guards' => [
            -----------
                'api' => [
                    'driver' => 'jwt',
                    'provider' => 'users',
                    'hash' => false,
                ],
            ],
        ```

    - Add  User.php Model...
        ```html
            
            use Tymon\JWTAuth\Contracts\JWTSubject;

            class User extends Authenticatable implements JWTSubject
            {
                ----
                /**
                * Get the identifier that will be stored in the subject claim of the JWT.
                *
                * @return mixed
                */
                public function getJWTIdentifier()
                {
                    return $this->getKey();
                }


                /**
                * Return a key value array, containing any custom claims to be added to the JWT.
                *
                * @return array
                */
                public function getJWTCustomClaims()
                {
                    return [];
                }
            }

        ```

    - Swagger Intigration :-
        - Command :-
            ```
                composer require "darkaonline/l5-swagger"
                php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
            ```

        - Add Swagger Base in Controller file...

            ```html
                <?php

                namespace App\Http\Controllers;

                use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
                use Illuminate\Foundation\Validation\ValidatesRequests;
                use Illuminate\Routing\Controller as BaseController;
                    /**
                        * @OA\Info(
                        *    title="Your super  ApplicationAPI",
                        *    version="1.0.0",
                        * )
                        * @OA\SecurityScheme(
                        *        type="http",
                        *        description="Login with email and password to get the authentication token",
                        *       name="Token based Based",
                        *        in="header",
                        *        scheme="bearer",
                        *        bearerFormat="JWT",
                        *        securityScheme="apiAuth",
                        * ),
                    */
                class Controller extends BaseController
                {
                    use AuthorizesRequests, ValidatesRequests;
                }

            ```

    - Create Controller
        - Command :-
            ```
                php artisan make:controller AuthController
            ```

        - Inside [AuthController] file...

        [AuthController]: https://github.com/dharmilweb/2_Api_Jwt_Authentication/blob/main/app/Http/Controllers/AuthController.php

    - Create Middleware
        - Command :-
            ```
                php artisan make:middleware JwtMiddleware
            ```

        - Inside [JwtMiddleware] file...

            ```html
                use JWTAuth;
                use Exception;
                
                class JwtMiddleware
                {
                    /**
                    * Handle an incoming request.
                    *
                    * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
                    */
                    public function handle(Request $request, Closure $next): Response
                    {
                        try {
                            $user = JWTAuth::parseToken()->authenticate();
                        } catch (Exception $e) {
                            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                                return response()->json(['status' => 'Token is Invalid'],401);
                            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                                return response()->json(['status' => 'Token is Expired'],401);
                            }else{
                                return response()->json(['status' => 'Authorization Token not found'],401);
                            }
                        }
                        return $next($request);
                    }

                }
            ```

    - Add Middleware in Kernel.php file...
        ```html
            protected $routeMiddleware = [
                ---------
                
                    'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class,
                    'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
                    'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
                ---------
            ]
        ```

    - Create api.php file...

        ```html
            use App\Http\Controllers\AuthController;

            Route::post('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/login', [AuthController::class, 'login'])->name('login');

            Route::group(['middleware' => 'jwt.verify','prefix' => 'auth'], function ($router) {

                Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
                Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
                Route::post('/me', [AuthController::class, 'me'])->name('me');
            });
        ```
                
    - Run Swagger... [ First Create AuthController Then Run Swagger Command ]
        - Command :-
        ```
            php artisan l5-swagger:generate
        ```

    - Run Laravel Project...
        - Command :-
            ```
                php artisan serve
            ```

        - Url :-
            ```
                http://localhost:8000/api/documentation
            ```
                
        <p align="center"><a href="https://raw.githubusercontent.com/dharmilweb/2_Api_Jwt_Authentication/main/public/Api_Auth/Input_1.png" target="_blank"><img src="https://github.com/dharmilweb/2_Api_Jwt_Authentication/blob/main/public/Api_Auth/Input_1.png" width="400" alt="Laravel Logo"></a></p>

## Authentications
Laravel having different types of `Authentication` for Web & Api Checkout its.

- [Web Authentication]
- [Api Jwt Authentication]
- [Api Sanctum Authentication]

[Web Authentication]: https://github.com/dharmilweb/1_Web_Authentication
[Api Jwt Authentication]: https://github.com/dharmilweb/2_Api_Jwt_Authentication
[Api Sanctum Authentication]: https://github.com/dharmilweb/3_Api_Sanctum_Auth

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).