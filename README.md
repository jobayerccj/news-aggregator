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

## License

The project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
