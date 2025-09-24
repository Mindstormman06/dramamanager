# QSS Drama Manager 🎭

The QSS Drama Manager is a web-based application designed to streamline the management of drama productions. It provides tools for organizing shows, characters, props, costumes, scripts, and more, making it easier for teachers, students, and administrators to collaborate effectively.

---

## Features

### 1. **Shows Management**
- Add, edit, and delete shows.
- Upload scripts (PDF format) and analyze them to extract characters and lines.
- Organize shows by year and semester.

### 2. **Character Management**
- Add, edit, and delete characters.
- Link characters to students or manually assign real names.
- Track mentions and line counts for each character.

### 3. **Props Management**
- Add, edit, and delete props.
- Upload photos for props and categorize them by location, condition, and type.
- Link props to specific shows.

### 4. **Costume Management**
- Add, edit, and delete costumes.
- Organize costumes by categories, styles, and conditions.
- Link costumes to characters.

### 5. **Script Analysis**
- Upload scripts in PDF format.
- Automatically detect character names and their mentions/lines.
- Create shows and characters directly from analyzed scripts.

### 6. **Ideas Planner**
- Save and manage future line ideas.
- Organize ideas by quotes and authors.

### 7. **User Management**
- Teacher and student signup with role-based access.
- Link teachers and students for collaboration.
- Reset passwords and manage user accounts.

### 8. **Rehearsal Scheduling**
- Create rehearsals involving specific students.
- Send notifications to a Discord bot informing the students.

---

## Project Structure

```
├── backend/
│   ├── db.php                # Database connection
│   ├── album/                # Backend logic for photo album
│   ├── characters/           # Backend logic for characters
│   ├── costumes/             # Backend logic for costumes
│   ├── ideas/                # Backend logic for ideas
│   ├── props/                # Backend logic for props
│   ├── schedule/             # Backend logic for schedule
│   ├── script_conversion/    # Python utilities for script formatting
│   ├── scripts/              # Python and PHP scripts for script analysis
│   ├── shows/                # Backend logic for shows
│   └── users/                # Backend logic for user management
├── album/                    # Photo album page
├── bot/                      # Discord bot settings and JS
├── characters/               # Character management pages
├── costumes/                 # Costume management pages
├── ideas/                    # Ideas planner pages
├── props/                    # Prop management pages
├── schedule/                 # Rehearsal scheduling page
├── scripts/                  # Script analysis and show creation pages
├── shows/                    # Show management pages
├── schedule/                 # Rehearsal scheduling pages
├── uploads/                  # Uploaded files (scripts, photos, etc.)
├── users/                    # User management pages
├── vendor/                   # Installed requirements (composer, parsedown, etc.)
├── index.php                 # Main dashboard
├── header.php                # Header template
├── footer.php                # Footer template
├── changelog.php             # Changelog Page
├── mascot.php                # Shows the page mascot
├── router.php                # URL routes for the site
└── README.md                 # Project documentation
```

---

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL database
- Python 3.8 or higher with the `PyMuPDF` library installed (`pip install pymupdf`)
- A web server (e.g., Apache with XAMPP)

### Steps
1. Clone the repository to your local machine:
   ```bash
   git clone <repository-url>
   ```
2. Import the database:
   - Use the `qssdrama79.sql` file to set up the database schema and initial data.
3. Configure the database connection:
   - Update the `backend/db.php` file with your database credentials.
4. Set up file permissions:
   - Ensure the `uploads/` directory is writable by the web server.
5. Install Python dependencies:
   ```bash
   pip install pymupdf
   ```
6. Start your web server and access the application via `http://localhost/<project-folder>`.

---

## Usage

1. **Login**:
   - Teachers and students can log in using their credentials.
2. **Manage Shows**:
   - Navigate to the "Shows" section to add or edit productions.
3. **Analyze Scripts**:
   - Upload a script in the "Script Import" section to extract characters and create a show.
4. **Manage Characters, Props, and Costumes**:
   - Use the respective sections to organize production elements.
5. **Plan Ideas**:
   - Save future line ideas in the "Ideas Planner" section.

---

## Technologies Used

- **Frontend**: HTML, CSS (TailwindCSS), JavaScript
- **Backend**: PHP (PDO for database interactions)
- **Database**: MySQL
- **Script Analysis**: Python (PyMuPDF)

---

## Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Commit your changes and push them to your fork.
4. Submit a pull request with a detailed description of your changes.

---

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

---
