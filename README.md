# BPPU Data Extractor

This PHP application allows you to extract structured tax data from PDFs (packaged in a ZIP file) into an Excel file. It is designed to work offline and process documents securely without saving files on the server.

## 🔧 Features

- Upload a ZIP file containing PDF files
- Extract NPWP, object tax codes, DPP, tariff, PPh, and document metadata
- Export results into a downloadable Excel (.xlsx) file
- Temporary files are auto-deleted after processing

## 📦 Installation (Local)

Follow these steps to run the project on your local machine.

### 1. Clone the Repository

```bash
git clone https://github.com/akhalidyahya/bppu-data-extractor.git
cd bppu-data-extractor
```

### 2. Install PHP Dependencies

Make sure you have [Composer](https://getcomposer.org/) installed.

```bash
composer install
```

This will install:
- `smalot/pdfparser` – for extracting text from PDF
- `phpoffice/phpspreadsheet` – for generating Excel output

### 3. Start Local PHP Server

You can run it using PHP's built-in server:

```bash
php -S localhost:8000
```

Then open your browser and go to:

```
http://localhost:8000/index.php
```

### 4. Upload and Extract

- Use the form on `index.php` to upload your ZIP file.
- Wait for the Excel download to begin automatically.
- No files are stored; everything is cleaned up after processing.

## 🔐 Privacy Notice

> This tool does **not save any files** on the server. All uploaded ZIP contents are extracted into a temporary directory and deleted immediately after processing is completed.

## 📄 License

[MIT License](https://mit-license.org/)

## 🔗 Source Code

View the full source code on GitHub:  
https://github.com/akhalidyahya/bppu-data-extractor
