FROM python:3.10-slim

WORKDIR /app

COPY . .

RUN apt update && apt install -y curl
RUN pip install flask

EXPOSE 8080

CMD ["python", "app.py"]
