# UHF RFID Tag Monitoring System Setup Guide

This project reads RFID tag scans from a hardware reader and displays them in real time on a web page.

---

## 1. Things You Need to Install First (Prerequisites)

Before starting, make sure your computer has the following software installed:

1. **Node.js** (v18 or newer) - Download from [nodejs.org](https://nodejs.org)
2. **PHP** (v8.2 or newer) - Download from [php.net](https://www.php.net)
3. **Composer** - Download from [getcomposer.org](https://getcomposer.org)
4. **Git** - Download from [git-scm.com](https://git-scm.com)
5. **Hardware RFID Reader** (G-Series / RealOpenIoT reader connected to your local network)

---

## 2. First-Time Setup (Do This Only Once)

Follow these exact steps when setting up the project for the first time.

### Step 2.1: Open a Command Prompt window
1. Press `Windows Key + R` on your keyboard.
2. Type `cmd` and press `Enter`.
3. Navigate to the project folder. For example:
   ```cmd
   cd "C:\Programs\___INTERN_PROJECTS\Online Building Permit System\uhf-rfid-monitoring\backend"
   ```

### Step 2.2: Create the `.env` Configuration File
Run this command inside the `backend` folder to copy the example configuration file:
```cmd
copy .env.example .env
```

### Step 2.3: Install Backend Dependencies
Run this command to download required PHP files:
```cmd
composer install
```

### Step 2.4: Install Frontend Dependencies
Run this command to download required JavaScript files:
```cmd
npm install
```

### Step 2.5: Generate Application Key
Run this command to generate an encryption key for Laravel:
```cmd
php artisan key:generate
```

### Step 2.6: Set Up Database Tables
Run this command to create all needed database tables:
```cmd
php artisan migrate
```

---

## 3. Configure Your RFID Reader Connection

Open the file `backend/.env` using Notepad or VS Code, and scroll to the bottom. Update these settings to match your RFID reader device:

```env
RFID_READER_IP=192.168.1.168
RFID_READER_PORT=8160
RFID_ANTENNAS=1,2,3,4
```

* **RFID_READER_IP**: The IP address assigned to your physical RFID reader.
* **RFID_READER_PORT**: The port number of your RFID reader (default is `8160`).
* **RFID_ANTENNAS**: Antenna numbers to read from, separated by commas.

---

## 4. How to Run the Project

You can run the project using **Method 1** (Quick launcher) or **Method 2** (Manual terminals).

### Method 1: Easy Automatic Start (Recommended)

1. Open the project root folder in Windows File Explorer.
2. Double-click the file named **`run.bat`**.
3. Four command windows will open automatically to start all services.
4. After 5 seconds, your default web browser will open at `http://127.0.0.1:8000`.

#### How to Stop the Project:
To close all background windows safely, double-click the file named **`stop.bat`**.

---

### Method 2: Manual Start (Using 4 Terminals)

If you prefer to start each process manually, open 4 separate Command Prompt windows.

* **Window 1 (Web Server):**
  ```cmd
  cd backend
  php artisan serve --port=8000
  ```

* **Window 2 (WebSocket Server for Real-Time Tags):**
  ```cmd
  cd backend
  php artisan reverb:start
  ```

* **Window 3 (Vite Frontend Build Tool):**
  ```cmd
  cd backend
  npm run dev
  ```

* **Window 4 (RFID Hardware Reader Bridge):**
  ```cmd
  cd backend\rfid-bridge
  node read-epc.js
  ```

Once all 4 terminals are running, open your web browser and visit `http://127.0.0.1:8000`.

---

## 5. How to Test Tag Scans

1. Open `http://127.0.0.1:8000` in your web browser.
2. Hold an RFID tag near the reader antenna.
3. The scanned EPC code, antenna number, and timestamp will immediately show up on the web screen.
4. Scanned tags are also saved automatically to `backend/rfid-bridge/scanned_tags.csv`.

---

## 6. Common Issues & Solutions

* **Problem: `'node'`, `'php'`, or `'composer'` is not recognized**
  * *Fix:* Install the missing program from Section 1 and make sure to check the box "Add to PATH" during installation. Restart your command prompt after installing.

* **Problem: `[!] Failed to start RFID reader: connect ETIMEDOUT`**
  * *Fix:* Verify that your computer and RFID reader are on the same local network subnet, and check that `RFID_READER_IP` in `backend/.env` matches your device IP address.

* **Problem: Page loads but scans do not update in real time**
  * *Fix:* Make sure the Reverb WebSocket server terminal (`php artisan reverb:start`) is running without errors.
