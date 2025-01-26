## News Aggregator

News aggregator service that pulls articles from various sources and provides endpoints for a frontend application to consume.

## Available API endpoints
https://www.postman.com/cloudy-crescent-872614/news-aggregator-v1/request/q08jje4/request-password-reset-link

## Requirements
1. docker, docker compose, PHP >= 8.2

## Installation
1. Install necessary dependencies

    ```bash
    composer install
    ```

2. Add .env file and update database connection information
    *   `cp .env-example .env`
    *   Update database credentials in the `.env` file. For example:
        ```
        DB_DATABASE=your_database_name
        DB_USERNAME=your_database_user
        DB_PASSWORD=your_database_password
        ```
    *   Generate application key
        ```
        php artisan key:generate
        ```

3. Start project 
    ```bash
    ./vendor/bin/sail up
    ```

4. Run necessary migrations 

    ```bash
    ./vendor/bin/sail artisan migrate

    ./vendor/bin/sail artisan scout:import App\\Models\\Article
    ```

5. Access container's shell
    ```
    ./vendor/bin/sail shell
    ```

6. Analyze code using php-cs-fixer (instead of changing anything automatically, it will show recommendation & we need to confirm it manually)

    ```
    ./vendor/bin/php-cs-fixer fix --dry-run --diff
    ```

    If php-cs-fixer is showing error for PHP version, plz execute it
    ```
    export PHP_CS_FIXER_IGNORE_ENV=1
    ```

7. Run news aggregator command
    ```
    php artisan news:collect {apiName}
    ```
    Available api names are newsapi, nytimes, guardian

8. Analyze code using php-stan
    ```
    ./vendor/bin/phpstan analyse
    ```

9. Testing
    *   `cp .env .env.testing`

    *   Create database for testing
        ```
        touch database/testing.sqlite
        ```

    *   Update database credentials in the `.env.testing` file. Example:
        ```
        DB_CONNECTION=sqlite
        DB_DATABASE=database/testing.sqlite
        ```

    *   Run unit test cases
        ```
        ./vendor/bin/sail php artisan test --testsuite Unit
        ```

    *   Run fearure test cases
        ```
        ./vendor/bin/sail php artisan test --testsuite Feature
        ```

    *   Check test coverage for feature test
        ```
        ./vendor/bin/sail php artisan test --testsuite=Feature --coverage-html=storage/test-coverage
        ```

    *   Check test coverage for unit test
        ```
        ./vendor/bin/sail php artisan test --testsuite=Unit --coverage-html=storage/test-coverage
        ```

## License

The project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
