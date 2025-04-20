<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GlaamSquad Billing</title>
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
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      border-radius: 10px;
    }
    h1, h2, h3 {
      color: #333;
    }
    form {
      margin-bottom: 20px;
    }
    input, button {
      display: block;
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      background-color: #007bff;
      color: #fff;
      cursor: pointer;
      border: none;
    }
    button:hover {
      background-color: #0056b3;
    }
    .hidden {
      display: none;
    }
    .bill {
      padding: 20px;
      background-color: #f9f9f9;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>GlaamSquad Billing</h1>
    <form id="searchCustomerForm">
      <input type="text" id="customerPhone" placeholder="Enter Customer Phone Number" required>
      <button type="submit">Search Customer</button>
    </form>
    <div id="customerDetails" class="hidden">
      <h2>Customer Details</h2>
      <p id="customerName"></p>
      <p id="customerEmail"></p>
      <p id="customerAddress"></p>
      <h3>Services</h3>
      <ul id="serviceList"></ul>
      <h3>Total Cost: <span id="totalCost"></span></h3>
      <button onclick="promptGST()">Generate Bill</button>
      <div id="bill" class="bill hidden">
        <h3>Bill</h3>
        <p id="billDetails"></p>
        <p>Total Cost with GST: <span id="totalCostWithGST"></span></p>
        <button onclick="printBill()">Print Bill</button>
        <button onclick="markAsPaid()">Mark as Paid</button>
        <button onclick="sendWhatsApp()">Send Bill via WhatsApp</button>
      </div>
    </div>
  </div>

  <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-database.js"></script>
  <script>
    const firebaseConfig = {
            apiKey: "########################",
            authDomain: "########################",
            databaseURL: "########################",
            projectId: "########################",
            storageBucket: "########################",
            messagingSenderId: "########################",
            appId: "########################",
            measurementId: "########################"
        };

    firebase.initializeApp(firebaseConfig);
    const database = firebase.database();

    document.getElementById('searchCustomerForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const phone = document.getElementById('customerPhone').value;
      searchCustomer(phone);
    });

    function searchCustomer(phone) {
      database.ref('customers').orderByChild('phone').equalTo(phone).once('value').then(snapshot => {
        if (snapshot.exists()) {
          const customer = Object.values(snapshot.val())[0];
          document.getElementById('customerName').innerText = `Name: ${customer.name}`;
          document.getElementById('customerEmail').innerText = `Email: ${customer.email}`;
          document.getElementById('customerAddress').innerText = `Address: ${customer.address}`;
          
          const serviceList = customer.services || {};
          const unpaidServices = Object.values(serviceList).filter(service => !service.paid);
          const serviceItems = unpaidServices.map(service => `<li>${service.type} on ${service.sittings.join(', ')}, Cost: ${service.cost}</li>`).join('');
          document.getElementById('serviceList').innerHTML = serviceItems;

          const totalCost = unpaidServices.reduce((sum, service) => sum + parseFloat(service.cost), 0);
          document.getElementById('totalCost').innerText = `${totalCost.toFixed(2)}`;
          
          document.getElementById('customerDetails').classList.remove('hidden');
        } else {
          alert('Customer not found');
        }
      }).catch(error => {
        console.error('Error fetching customer: ', error);
      });
    }

    function promptGST() {
      const addGST = confirm("Do you want to add 18% GST?");
      const totalCost = parseFloat(document.getElementById('totalCost').innerText);
      let totalCostWithGST = totalCost;

      if (addGST) {
        totalCostWithGST = totalCost * 1.18;
      }

      document.getElementById('billDetails').innerText = `Services:\n${document.getElementById('serviceList').innerText}`;
      document.getElementById('totalCostWithGST').innerText = `${totalCostWithGST.toFixed(2)}`;
      document.getElementById('bill').classList.remove('hidden');
    }

    function printBill() {
      window.print();
    }

    function markAsPaid() {
      const phone = document.getElementById('customerPhone').value;
      database.ref('customers').orderByChild('phone').equalTo(phone).once('value').then(snapshot => {
        if (snapshot.exists()) {
          const customerKey = Object.keys(snapshot.val())[0];
          const customer = snapshot.val()[customerKey];
          const updatedServices = Object.keys(customer.services || {}).reduce((result, key) => {
            if (!customer.services[key].paid) {
              result[key] = {
                ...customer.services[key],
                paid: true
              };
            } else {
              result[key] = customer.services[key];
            }
            return result;
          }, {});

          database.ref(`customers/${customerKey}/services`).set(updatedServices).then(() => {
            alert('Services marked as paid');
            searchCustomer(phone);
          }).catch(error => {
            console.error('Error updating services: ', error);
          });
        }
      }).catch(error => {
        console.error('Error fetching customer: ', error);
      });
    }

    function sendWhatsApp() {
      const phone = document.getElementById('customerPhone').value;
      const name = document.getElementById('customerName').innerText;
      const services = document.getElementById('serviceList').innerText;
      const totalCostWithGST = document.getElementById('totalCostWithGST').innerText;
      const message = `Bill Details:\n${name}\nServices:\n${services}\nTotal Cost with GST: ${totalCostWithGST}`;
      const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank');
    }
  </script>
</body>
</html>
