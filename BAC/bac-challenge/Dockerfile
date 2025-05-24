FROM python:3.10
WORKDIR /app
COPY requirements.txt requirements.txt
RUN pip install -r requirements.txt
COPY app/ .
EXPOSE 5000
CMD ["python", "app.py"]
