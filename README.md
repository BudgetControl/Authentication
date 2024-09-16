# BudgetControl Workspace

This repository contains the workspace for managing the BudgetControl application.

## Prerequisites

- Docker: [Install Docker](https://docs.docker.com/get-docker/)
- Task: [Install Task](https://taskfile.dev/#/installation)

## Getting Started

1. Clone this repository:

    ```bash
    git clone https://github.com/your-username/budgetcontrol-workspace.git
    ```

2. Build and run the Docker containers:

    ```bash
    task build:dev
    ```

5. Open your browser and visit [http://localhost:8082](http://localhost:8082) to access the BudgetControl application.

## Build dev enviroment
- docker-compose -f docker-compose.yml -f -f docker-compose.db.yml up -d
- docker container cp bin/apache/default.conf budgetcontrol-ms-authentication:/etc/apache2/sites-available/budgetcontrol.cloud.conf
- docker container exec budgetcontrol-ms-authentication service apache2 restart

## Run PHP Tests
- docker exec budgetcontrol-ms-authentication bash -c "vendor/bin/phinx rollback -t 0 && vendor/bin/phinx migrate && vendor/bin/phinx seed:run" 
- docker exec budgetcontrol-ms-authentication vendor/bin/phpunit test


## Contributing

Contributions are welcome! Please read our [Contribution Guidelines](CONTRIBUTING.md) for more information.

## License

This project is licensed under the [MIT License](LICENSE).