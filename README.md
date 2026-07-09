# Setup Guide 📝

<p align="center">
  <img width="736" height="580" alt="blueprint" src="https://github.com/user-attachments/assets/af08b2c3-d725-4380-bc85-acd0f517d5bb" />
</p>

## Prerequisites

- PHP 8.2+
- Composer
- Node.js
- Python 3.10.x
- UHF RFID Reader (RealOpenIoT G-Series)

**Troubleshoot:** [ask ChatGPT](https://chatgpt.com)

---

## Step 1: Install the Reader SDK

```bash
pip install uhfReaderApi==1.0.4
```

Open `example/ReadEpc.py` and change the reader IP here:
```python
if g_client.openTcp(("192.168.1.168", 8160)):
```

**Troubleshoot:** [ask ChatGPT](https://chatgpt.com)

---

## Step 2: Backend Setup

```bash
cd backend
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan reverb:install
php artisan migrate
```

**Troubleshoot:** [ask ChatGPT](https://chatgpt.com)

---

## Step 3: Run It

Open 4 terminals in `backend`:

```bash
php artisan serve --port=8000
```
```bash
php artisan reverb:start
```
```bash
npm run dev
```

Then one more terminal in `example`:
```bash
python ReadEpc.py
```

Or just run `python run.py` at the repo root to launch everything at once (Windows only). `python stop.py` kills it.

**Troubleshoot:** [ask ChatGPT](https://chatgpt.com)

---

## Step 4: View It

```
http://127.0.0.1:8000/
```

Live table of scanned tags. Search, click a row to copy EPC, export to CSV.

**Troubleshoot:** [ask ChatGPT](https://chatgpt.com)

---

## Well Done 👏

<p align="center">
  <img width="498" height="498" alt="clapping-leonardo-dicaprio" src="https://github.com/user-attachments/assets/385f0c79-0ec4-4d33-991a-758b18524b06" />
</p>
