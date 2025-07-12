BookChef – Project Documentation
       INDEX - OF FOLLOWING DOCUMENTAION

·	Project Title
·	Objective
·	Technologies Used
·	Features
   • User Features
   • Admin Features
·	Database Structure
   • users
   • chefs
   • bookings
·	Pages Overview
·	Booking Workflow
·	Future Scope
·	Folder Structure
·	Conclusion



1. Project Title
BookChef – Chef Booking Web Application
2. Objective
To develop a real-world web platform where users can:
 - View and book chefs
 - Choose between 'Pay Now' and 'Pay Later'
 - Manage bookings in their profile
 - Cancel unpaid bookings
 - Admins can manage chef listings and availability
3. Technologies Used
Component	Stack
Frontend	HTML, Tailwind CSS
Backend	PHP
Database	MySQL
Server	XAMPP (Localhost)
Payment Flow	Google capcha
4. Features
Users
- Sign up / Log in
 - View chef list with availability
 - Book chef and:
   - Pay immediately (payment_status = 'paid')
   - Pay later (payment_status = 'pending')
 - Cancel unpaid bookings
 - View profile, past bookings, and status
Admin
- Log in via separate panel
 - Add/edit/delete chefs
 - Toggle chef availability (Available/Unavailable)
 - View booking list sorted by date
5. Database Structure
users
Field	Type
id	INT (PK)
name	VARCHAR
email	VARCHAR
phone	VARCHAR
address	TEXT
profile_pic	VARCHAR


 chefs
Field	Type
id	INT (PK)
name	VARCHAR
specialty	VARCHAR
experience	VARCHAR
fees	INT
picture	VARCHAR
is_available	TINYINT

 bookings
Field	Type
id	INT (PK)
user_id	INT (FK)
chef_id	INT (FK)
event_date	DATE
event_time	TIME
event_place	TEXT
phone	VARCHAR
fees	INT
status	VARCHAR (active, canceled)
payment_status	VARCHAR (pending, paid)
payment_id	VARCHAR
created_at	TIMESTAMP
6. Pages Overview
Page Name	Description
index.php	Chef dashboard for users
confirm-booking.php	Booking form with Pay Now/Later
continue-booking.php	Handles booking insertion
payment.php	Simulates real payment, marks as paid
profile.php	Shows booking history, cancel/pay
admin/dashboard.php	Admin panel
7. Booking Workflow
1. User selects available chef → clicks Book Now
 2. On next page:
    - Pay Now → inserts with paid status and redirects to profile
    - Pay Later → inserts with pending status, cancel available
 3. User sees status and can cancel pending bookings
8. Future Scope
- Integrate Razorpay/PhonePe API for real payments
 - Host on a cloud platform (like Vercel or InfinityFree)
 - Add email/SMS confirmation
 - Chef calendar-based availability
 - Admin analytics dashboard



9.Folder structure:
bookchef/
│
├── admin/
│   ├── add-chef.php
│   ├── booking.php
│   ├── chefs.php
│   ├── dashboard.php
│   ├── delete-chef.php
│   ├── delete-user.php
│   ├── toggle-chef.php
│   └── users.php
│
├── assets/
│   └── uploads/
│       ├── pic_file/
│       └── qr-sample.jpeg
│
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
│
├── config/
│   └── db.php
│
├── User-book-chef.php
├── booking.php
├── index.php
├── edit.php
├── cancel.php
└── profile.php
                         
10. Conclusion
BookChef simulates a real chef booking platform with full frontend and backend integration. It supports live availability, payment status, and user interaction, making it a great real-world project for learning full-stack PHP development.

