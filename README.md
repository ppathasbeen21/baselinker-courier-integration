# Baselinker Courier Integration task

The goal is to create a shipment via API and download the shipping label (PDF) in the browser.

## ✅ Features

- PHP native implementation (no frameworks, no Composer)
- Follows PSR-12 coding standard
- Uses `curl` and `json_encode` to interact with the Baselinker Recruitment API
- Handles API errors and connection issues clearly
- Automatically truncates address fields to match API limits
- Generates and forces download of a valid PDF label
- Dockerized environment with Apache and PHP 8.4

---

## 🐳 How to run

### Requirements

- Docker
- Docker Compose

### Steps

1. Clone this repository:
   ```bash
   git clone git@github.com:ppathasbeen21/baselinker-courier-integration.git
   cd baselinker-courier-integration

2. Run Docker Compose to start the Apache server with PHP:
   ```bash
   docker compose up -d

3. Access the application in your web browser:
   ```
   http://localhost:8080/spring.php
   
## ✅ What it implements from the task

- newPackage(array $order, array $params) with all required fields
- Handles required API fields: Service, Weight, Value, Currency, Products, etc.
- Truncates values to respect field length limits
- Uses only built-in PHP features (no libs/frameworks)
- Fully PSR-12 compatible
- Gracefully handles and displays API or connection errors
- packagePDF(string $trackingNumber) downloads PDF via browser
- Label is downloaded directly (forced save)

### Official documentation

https://developers.baselinker.com/recruitment/docs/?hash=xgOHJOqSVDLHrTY4d0S9jMO0Nz4MI0yzh3A4o0vUml1QS0M0lo