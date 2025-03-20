# FitZone

FitZone is a comprehensive fitness web application designed to help users manage their fitness routines, book classes, and monitor their memberships. The platform offers a range of features, including class bookings, membership management, and user dashboards, to enhance the fitness experience.

## Features

- **User Registration and Authentication**: Users can create accounts and securely log in to access personalized features.
- **Class Booking**: Browse available fitness classes and book sessions directly through the platform.
- **Membership Management**: View and manage membership details, including subscription status and renewal dates.
- **User Dashboard**: Access a personalized dashboard to track bookings, memberships, and profile information.

## Installation

To set up the FitZone application locally, follow these steps:

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/poojithakiriyalagammana/fitzone.git
   ```
2. **Navigate to the Project Directory**:
   ```bash
   cd fitzone
   ```
3. **Set Up the Database**:
   - Create a MySQL database named `fitzone`.
   - Import the provided SQL file located in the `database` directory to set up the necessary tables.
4. **Configure the Application**:
   - Update the `includes/db_connect.php` file with your database credentials.
5. **Start the Application**:
   - Deploy the application on a local server (e.g., XAMPP, WAMP) and navigate to `http://localhost/fitzone` in your browser.

## Usage

- **Register**: Create a new account using the [registration page](register.php).
- **Log In**: Access your account through the [login page](login.php).
- **Book Classes**: Browse and book available classes via the [classes page](classes.php).
- **Manage Membership**: View and update your membership details on the [membership page](membership.php).
- **Dashboard**: Access your personalized dashboard at [dashboard.php](dashboard.php) to monitor your activities.

## Contributing

We welcome contributions to enhance FitZone. To contribute:

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Commit your changes with clear descriptions.
4. Push your branch to your forked repository.
5. Submit a pull request detailing your changes.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

## Acknowledgements

We extend our gratitude to all contributors and users who support and improve FitZone.
