# Gunakan base image python
FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Install dependencies
COPY requirements.txt requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Copy semua file project
COPY . .

# Expose port Flask
EXPOSE 5000

# Jalankan Flask app
CMD ["python", "app.py"]
