## News Aggregator

News aggregator service that pulls articles from various sources and provides endpoints for a frontend application to consume.:

## Requirements
1. docker, docker compose

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

4. run necessary migrations 

    ```bash
    ./vendor/bin/sail artisan migrate
    ```

5. Analyze code using php-cs-fixer (instead of changing anything automatically, it will show recommendation & we need to confirm it manually)

    ```
    ./vendor/bin/php-cs-fixer fix --dry-run --diff
    ```

    If php-cs-fixer is showing error for PHP version, plz execute it
    ```
    export PHP_CS_FIXER_IGNORE_ENV=1
    ```

6. Analyze code using php-stan
    ```
    ./vendor/bin/phpstan analyse
    ```

7. Testing
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
