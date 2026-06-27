# Database

struttura: tabelle mariadb -> users, projects, tasks, subtasks
tabella sqlite -> time-records (id, userid, subtaskid, startTime, endTime, description, metadata)

# Debug php code
for debugging -> use error_log()


# OpenApi accessor example (on public methods)
/**
 * @openapi
 * path: /users
 * method: POST
 * summary: Create a brand new user record with configuration parameters
 * header: Authorization | string | required | Bearer token format
 * query: send_email | boolean | optional | Automatically trigger a registration welcome email
 * query: profile_type | string | required | Tier class assignment (e.g., standard, admin)
 * body: name | string | required | The full name of the user | John Doe
 * body: email | string | required | A unique email address | john@example.com
 * response: 201 | User successfully created
 * response: 400 | Missing required fields
 */