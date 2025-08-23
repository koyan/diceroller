FROM python:3.11-slim

# Install dependencies
RUN pip install --no-cache-dir flask flask-socketio eventlet gunicorn

# Set working directory
WORKDIR /app

# Copy app code
COPY . /app

# Command: run with gunicorn + eventlet worker
CMD ["gunicorn", "-k", "eventlet", "-w", "1", "-b", "0.0.0.0:5000", "app:app"]
