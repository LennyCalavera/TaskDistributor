Simple REST API app based on Laravel that distributes unique set of various coding tasks to users once a day.

The app supports simple API authorisation via Laravel Sanctum.
Authorized users can mark received tasks as solved and also replace them.

Repository configured to run via Docker, use command "docker-compose up --build -d"

Repository also contains Postman project configs for testing:
- TaskDistibutor.postman_collection.json
- TaskDistributor.postman_environment.json

Routing:
- /api/auth/login
- /api/auth/register
- /api/auth/logout
- /api/auth/user
- /api/task/get
- /api/task/solve
- /api/task/skip