<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GlaamSquad Admin</title>
  <style>
    body {
        font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f7f7f7;
      background-image: url('logo.jpg');
      background-repeat: no-repeat;
      background-attachment: fixed;  
      background-size: cover;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .navbar {
      background-color: #333;
      overflow: hidden;
    }
    .navbar a {
      float: left;
      display: block;
      color: #f2f2f2;
      text-align: center;
      padding: 14px 16px;
      text-decoration: none;
    }
    .navbar a:hover {
      background-color: #ddd;
      color: black;
    }
    .navbar .datepicker-container {
      float: right;
      margin: 10px;
    }
    .navbar input[type="date"] {
      display: none;
      padding: 6px 10px;
      border: none;
      background: #ddd;
      color: black;
      cursor: pointer;
    }
    .section {
      display: none;
      padding: 20px;
    }
    .section.active {
      display: block;
    }
    h1, h2, h3 {
      color: #333;
    }
    form {
      margin-bottom: 20px;
    }
    input, select, button {
      display: block;
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      background-color: #28a745;
      color: #fff;
      cursor: pointer;
      border: none;
    }
    button:hover {
      background-color: #218838;
    }
    .customer-list, .customer-details, .notifications, .filtered-notifications {
      margin-top: 20px;
    }
    .customer-item {
      padding: 10px;
      border-bottom: 1px solid #ccc;
      cursor: pointer;
    }
    .customer-item:hover {
      background-color: #f1f1f1;
    }
    .service-list {
      list-style-type: none;
      padding: 0;
    }
    .service-list li {
      margin-bottom: 5px;
    }
    .hidden {
      display: none;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <a href="#home" onclick="showSection('home')">Home</a>
    <a href="#addCustomer" onclick="showSection('addCustomer')">Add Customer</a>
    <a href="#searchCustomer" onclick="showSection('searchCustomer')">Search Customer</a>
    <a href="#searchService" onclick="showSection('searchService')">Search Service</a>
    <a href="#allCustomers" onclick="showSection('allCustomers')">All Customers</a>
    <a href="#notifications" onclick="showSection('notifications')">Notifications</a>
    <a href="#todayBookings" onclick="showTodayBookings()">Today's Bookings</a>
    <a href="newww.php">Generate Bill</a>

    <div class="datepicker-container">
      <input type="date" id="datePicker" onchange="showBookingsByDate(this.value)" />
    </div>
  </div>

  <div class="container">
    <div id="home" class="section active">
      <h1>Welcome to GlaamSquad Admin Panel</h1>
      <p>Use the navigation bar to manage customers and services.</p>
    </div>

    <div id="addCustomer" class="section">
      <h2>Add Customer</h2>
      <form id="addCustomerForm">
        <input type="text" id="customerName" placeholder="Name" required>
        <input type="text" id="customerPhone" placeholder="Phone Number" required>
        <input type="email" id="customerEmail" placeholder="Email" required>
        <input type="text" id="customerAddress" placeholder="Address" required>
        <button type="submit">Add Customer</button>
      </form>
    </div>

    <div id="searchCustomer" class="section">
      <h2>Search Customer</h2>
      <form id="searchCustomerForm">
        <input type="text" id="searchCustomerPhone" placeholder="Phone Number" required>
        <button type="submit">Search</button>
      </form>
      <div id="customerList"></div>
      <div id="customerDetails"></div>
    </div>

    <div id="searchService" class="section">
      <h2>Search Service</h2>
      <form id="searchServiceForm">
        <input type="text" id="searchServiceType" placeholder="Service Type" required>
        <button type="submit">Search</button>
      </form>
      <div id="serviceCustomerList"></div>
      <div id="serviceCustomerDetails"></div>
    </div>

    <div id="allCustomers" class="section">
      <h2>All Customers</h2>
      <div id="allCustomerList" class="customer-list"></div>
    </div>

    <div id="notifications" class="section">
      <h2>Notifications</h2>
      <div id="notificationsList" class="notifications"></div>
    </div>

    <div id="todayBookings" class="section">
      <h2>Today's Bookings</h2>
      <div id="todayBookingsList" class="filtered-notifications"></div>
    </div>

    <div id="bookingsByDate" class="section">
      <h2>Bookings by Date</h2>
      <div id="bookingsByDateList" class="filtered-notifications"></div>
    </div>
  </div>

  <!-- Include Firebase Library -->
  <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-database.js"></script>

  <!-- Initialize Firebase -->
  <script>
    // Initialize Firebase
    const firebaseConfig = {
  apiKey: "AIzaSyBa186Bp6rjODJnjNiP4CSg4O-qXg0zAYE",
  authDomain: "glaamsquard.firebaseapp.com",
  databaseURL: "https://glaamsquard-default-rtdb.firebaseio.com",
  projectId: "glaamsquard",
  storageBucket: "glaamsquard.appspot.com",
  messagingSenderId: "1006973627746",
  appId: "1:1006973627746:web:a519d85160d2724cb5103b",
  measurementId: "G-J1Z93Q9MCZ"
};

    firebase.initializeApp(firebaseConfig);

    const database = firebase.database();

    // Add Customer
    document.getElementById('addCustomerForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const name = document.getElementById('customerName').value;
      const phone = document.getElementById('customerPhone').value;
      const email = document.getElementById('customerEmail').value;
      const address = document.getElementById('customerAddress').value;

      database.ref('customers/' + phone).set({
        name: name,
        phone: phone,
        email: email,
        address: address,
        services: []
      }).then(() => {
        alert('Customer added successfully');
        loadAllCustomers();
      }).catch(error => {
        console.error('Error adding customer: ', error);
      });
    });

    // Search Customer
    document.getElementById('searchCustomerForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const phone = document.getElementById('searchCustomerPhone').value;
      searchCustomer(phone);
    });

    // Search Customer Function
    function searchCustomer(phone) {
      database.ref('customers').orderByChild('phone').equalTo(phone).once('value').then(snapshot => {
        if (snapshot.exists()) {
          const customerList = Object.keys(snapshot.val()).map(customerId => {
            const customer = snapshot.val()[customerId];
            return `<div class="customer-item" onclick="showCustomerDetails('${customerId}')">${customer.name} - ${customer.phone}</div>`;
          }).join('');
          document.getElementById('customerList').innerHTML = customerList;
        } else {
          alert('Customer not found');
        }
      }).catch(error => {
        console.error('Error fetching customer: ', error);
      });
    }

    // Show Customer Details
    function showCustomerDetails(customerId) {
      database.ref('customers/' + customerId).once('value').then(snapshot => {
        const customer = snapshot.val();
        if (customer) {
          document.getElementById('customerDetails').innerHTML = 
            `<h3>Customer Details</h3>
            <p>Name: ${customer.name}</p>
            <p>Phone: ${customer.phone}</p>
            <p>Email: ${customer.email}</p>
            <p>Address: ${customer.address}</p>
            <h4>Services</h4>
            <ul class="service-list">${customer.services ? Object.values(customer.services).map(service => `<li>${service.type} on ${service.sittings.join(', ')}, Cost: ${service.cost}</li>`).join('') : 'No services available'}</ul>
            <button onclick="showAddServiceForm('${customerId}')">Add Service</button>`
          ;
        } else {
          document.getElementById('customerDetails').innerHTML = '<p>No customer details found.</p>';
        }
      }).catch(error => {
        console.error('Error fetching customer details: ', error);
      });
    }

    // Show Add Service Form
    function showAddServiceForm(customerId) {
      document.getElementById('customerDetails').innerHTML += 
        `<div>
          <h4>Add Service</h4>
          <form id="addServiceForm">
            <select id="serviceTypeDropdown" required>
              <!-- Service types will be populated dynamically -->
            </select>
            <input type="text" id="serviceType" class="hidden" placeholder="Service Type" required>
            <input type="number" id="serviceSittings" placeholder="Number of Sittings" required>
            <div id="sittingSchedules"></div>
            <input type="number" id="serviceCost" placeholder="Service Cost" required>
            <button type="submit">Add Service</button>
          </form>
        </div>`
      ;

      // Fetch and populate service types from the database
      database.ref('serviceTypes').once('value').then(snapshot => {
        const serviceTypes = snapshot.val();
        const serviceTypeDropdown = document.getElementById('serviceTypeDropdown');
        serviceTypeDropdown.innerHTML = '<option value="">Select Service Type</option>'; // Default option
        for (const key in serviceTypes) {
          const type = serviceTypes[key].type;
          serviceTypeDropdown.innerHTML += `<option value="${type}">${type}</option>`;
        }
        serviceTypeDropdown.innerHTML += '<option value="Other">Other</option>';
      }).catch(error => {
        console.error('Error fetching service types: ', error);
      });

      // Handle Service Type Dropdown
      document.getElementById('serviceTypeDropdown').addEventListener('change', function() {
        const serviceType = document.getElementById('serviceType');
        if (this.value === 'Other') {
          serviceType.classList.remove('hidden');
        } else {
          serviceType.classList.add('hidden');
          serviceType.value = ''; // Clear input field
        }
      });

      // Handle Number of Sittings
      document.getElementById('serviceSittings').addEventListener('input', function() {
        const sittingSchedules = document.getElementById('sittingSchedules');
        sittingSchedules.innerHTML = '';
        const numSittings = this.value;
        for (let i = 1; i <= numSittings; i++) {
          sittingSchedules.innerHTML += `<input type="date" id="sittingSchedule${i}" placeholder="Schedule for Sitting ${i}" required>`;
        }
      });

      // Add Service to Database
      document.getElementById('addServiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const serviceTypeDropdown = document.getElementById('serviceTypeDropdown').value;
        const serviceType = serviceTypeDropdown === 'Other' ? document.getElementById('serviceType').value : serviceTypeDropdown;
        const serviceCost = document.getElementById('serviceCost').value;
        const numSittings = document.getElementById('serviceSittings').value;
        const schedules = [];
        for (let i = 1; i <= numSittings; i++) {
          schedules.push(document.getElementById(`sittingSchedule${i}`).value);
        }

        // Save the new service type to the database if it's not in the list
        if (serviceTypeDropdown === 'Other') {
          database.ref('serviceTypes').push({ type: serviceType });
        }

        database.ref('customers/' + customerId + '/services').push({
          type: serviceType,
          cost: serviceCost,
          sittings: schedules
        }).then(() => {
          alert('Service added successfully');
          showCustomerDetails(customerId);
        }).catch(error => {
          console.error('Error adding service: ', error);
        });
      });
    }

    // Search Service
    document.getElementById('searchServiceForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const serviceType = document.getElementById('searchServiceType').value;
      searchService(serviceType);
    });

    // Search Service Function
    function searchService(serviceType) {
      database.ref('customers').once('value').then(snapshot => {
        if (snapshot.exists()) {
          const serviceCustomerList = [];
          snapshot.forEach(customerSnapshot => {
            const customer = customerSnapshot.val();
            const services = customer.services || {};
            Object.values(services).forEach(service => {
              if (service.type.toLowerCase() === serviceType.toLowerCase()) {
                serviceCustomerList.push({ customerId: customerSnapshot.key, customer });
              }
            });
          });

          if (serviceCustomerList.length) {
            const serviceCustomerItems = serviceCustomerList.map(item => {
              const customer = item.customer;
              return `<div class="customer-item" onclick="showServiceCustomerDetails('${item.customerId}')">${customer.name} - ${customer.phone} (${serviceType})</div>`;
            }).join('');
            document.getElementById('serviceCustomerList').innerHTML = serviceCustomerItems;
          } else {
            document.getElementById('serviceCustomerList').innerHTML = '<p>No customers found for the selected service.</p>';
          }
        } else {
          document.getElementById('serviceCustomerList').innerHTML = '<p>No customers found.</p>';
        }
      }).catch(error => {
        console.error('Error fetching customers by service: ', error);
      });
    }

    // Show Service Customer Details
    function showServiceCustomerDetails(customerId) {
      database.ref('customers/' + customerId).once('value').then(snapshot => {
        const customer = snapshot.val();
        if (customer) {
          document.getElementById('serviceCustomerDetails').innerHTML = 
            `<h3>Customer Details</h3>
            <p>Name: ${customer.name}</p>
            <p>Phone: ${customer.phone}</p>
            <p>Email: ${customer.email}</p>
            <p>Address: ${customer.address}</p>
            <h4>Services</h4>
            <ul class="service-list">${customer.services ? Object.values(customer.services).map(service => `<li>${service.type} on ${service.sittings.join(', ')}, Cost: ${service.cost}</li>`).join('') : 'No services available'}</ul>
            <a href="https://wa.me/${customer.phone}" target="_blank">
              <button>Send Enquiry via WhatsApp</button>
            </a>`
          ;
        } else {
          document.getElementById('serviceCustomerDetails').innerHTML = '<p>No customer details found.</p>';
        }
      }).catch(error => {
        console.error('Error fetching service customer details: ', error);
      });
    }

    // Load All Customers
    function loadAllCustomers() {
      database.ref('customers').once('value').then(snapshot => {
        if (snapshot.exists()) {
          const allCustomerList = Object.keys(snapshot.val()).map(customerId => {
            const customer = snapshot.val()[customerId];
            return `<div class="customer-item" onclick="showCustomerDetails('${customerId}')">${customer.name} - ${customer.phone}</div>`;
          }).join('');
          document.getElementById('allCustomerList').innerHTML = allCustomerList;
        } else {
          document.getElementById('allCustomerList').innerHTML = '<p>No customers found.</p>';
        }
      }).catch(error => {
        console.error('Error fetching all customers: ', error);
      });
    }

    // Load Notifications
    function loadNotifications() {
      database.ref('customers').once('value').then(snapshot => {
        if (snapshot.exists()) {
          const notifications = [];
          snapshot.forEach(customerSnapshot => {
            const customer = customerSnapshot.val();
            const services = customer.services || {};
            Object.values(services).forEach(service => {
              service.sittings.forEach(date => {
                notifications.push(`${service.type} for ${customer.name} on ${date}, Cost: ${service.cost}`);
              });
            });
          });
          document.getElementById('notificationsList').innerHTML = notifications.length ? notifications.map(notification => `<p>${notification}</p>`).join('') : '<p>No notifications found.</p>';
        } else {
          document.getElementById('notificationsList').innerHTML = '<p>No notifications found.</p>';
        }
      }).catch(error => {
        console.error('Error fetching notifications: ', error);
      });
    }

    // Show Today's Bookings
    function showTodayBookings() {
      const today = new Date().toISOString().split('T')[0];
      const datePicker = document.getElementById('datePicker');
      datePicker.value = today;
      datePicker.style.display = 'block';
      showBookingsByDate(today);
    }

    // Show Bookings by Date
    function showBookingsByDate(date) {
      database.ref('customers').once('value').then(snapshot => {
        if (snapshot.exists()) {
          const filteredBookings = [];
          snapshot.forEach(customerSnapshot => {
            const customer = customerSnapshot.val();
            const services = customer.services || {};
            Object.values(services).forEach(service => {
              if (service.sittings.includes(date)) {
                filteredBookings.push(`${service.type} for ${customer.name} on ${date}, Cost: ${service.cost}`);
              }
            });
          });
          const sectionId = date === new Date().toISOString().split('T')[0] ? 'todayBookingsList' : 'bookingsByDateList';
          document.getElementById(sectionId).innerHTML = filteredBookings.length ? filteredBookings.map(booking => `<p>${booking}</p>`).join('') : '<p>No bookings found for selected date.</p>';
          showSection(date === new Date().toISOString().split('T')[0] ? 'todayBookings' : 'bookingsByDate');
        } else {
          document.getElementById('todayBookingsList').innerHTML = '<p>No bookings found for today.</p>';
        }
      }).catch(error => {
        console.error('Error fetching bookings: ', error);
      });
    }

    // Section Navigation
    function showSection(sectionId) {
      const sections = document.querySelectorAll('.section');
      sections.forEach(section => {
        section.classList.remove('active');
      });
      document.getElementById(sectionId).classList.add('active');
      if (sectionId === 'notifications') {
        loadNotifications();
      }
      if (sectionId === 'todayBookings' || sectionId === 'bookingsByDate') {
        document.getElementById('datePicker').style.display = 'block';
      } else {
        document.getElementById('datePicker').style.display = 'none';
      }
    }

    // Initial load
    loadAllCustomers();
  </script>
</body>

</html>
