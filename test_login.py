import requests

url = "http://127.0.0.1:8000/api/login"

payload = {
    "email": "admin@example.com",
    "password": "admin"
}


response = requests.post(url, json=payload, headers={"Content-Type": "application/json"})

if response.status_code == 200:
    print("Connexion réussie !")
    print("Token JWT :", response.json().get("token"))
else:
    print("Échec de la connexion.")
    print("Code:", response.status_code)
