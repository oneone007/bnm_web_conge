from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
import logging
from cryptography.fernet import Fernet

app = Flask(__name__)
CORS(app)

# === Logging ===
logger = logging.getLogger(__name__)
logging.basicConfig(level=logging.INFO)

# === Encryption Key ===
# !! In production, store this key in a secure location (env variable or secrets manager)
ENCRYPTION_KEY = b'hukIqOCK3RGhnVUpZRN5qdZCe-Tu4wS5QDXAVo8Wick='  # replace with your own key if needed
cipher = Fernet(ENCRYPTION_KEY)

# === MySQL Connection ===
def get_localdb_connection():
    try:
        return mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="bnm",
            charset="utf8",
            use_unicode=True,
            autocommit=False
        )
    except mysql.connector.Error as err:
        logger.error(f"Error connecting to MySQL database: {err}")
        return None

# === Encrypt ===
def encrypt(text):
    return cipher.encrypt(text.encode()).decode()

# === Decrypt ===
def decrypt(token):
    return cipher.decrypt(token.encode()).decode()

# === Get List ===
@app.route("/list", methods=["GET"])
def list_wifi():
    conn = get_localdb_connection()
    cur = conn.cursor(dictionary=True)
    cur.execute("SELECT id, name, password, modempasswd, ip FROM wifi_passwords ORDER BY id DESC")
    result = cur.fetchall()
    conn.close()

    # Decrypt passwords before sending
    for row in result:
        try:
            row['password'] = decrypt(row['password'])
            row['modempasswd'] = decrypt(row['modempasswd'])
        except Exception as e:
            row['password'] = "[decryption error]"
            row['modempasswd'] = "[decryption error]"
            logger.error(f"Decryption error: {e}")

    return jsonify(result)

# === Add WiFi ===
@app.route("/add", methods=["POST"])
def add_wifi():
    data = request.get_json()
    conn = get_localdb_connection()
    cur = conn.cursor()

    try:
        encrypted_password = encrypt(data['password'])
        encrypted_modem = encrypt(data['modempasswd'])

        cur.execute("""
            INSERT INTO wifi_passwords (name, password, ip, modempasswd, created_by)
            VALUES (%s, %s, %s, %s, 'admin')
        """, (data['name'], encrypted_password, data['ip'], encrypted_modem))

        conn.commit()
        return jsonify({"success": True})
    except Exception as e:
        logger.error(f"Add WiFi error: {e}")
        return jsonify({"success": False, "error": str(e)})
    finally:
        conn.close()

# === Update WiFi ===
@app.route("/update/<int:id>", methods=["POST"])
def update_wifi(id):
    data = request.get_json()
    conn = get_localdb_connection()
    cur = conn.cursor()

    try:
        encrypted_password = encrypt(data['password'])
        encrypted_modem = encrypt(data['modempasswd'])

        cur.execute("""
            UPDATE wifi_passwords
            SET name = %s,
                password = %s,
                ip = %s,
                modempasswd = %s,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = %s
        """, (data['name'], encrypted_password, data['ip'], encrypted_modem, id))

        conn.commit()
        return jsonify({"success": True})
    except Exception as e:
        logger.error(f"Update WiFi error: {e}")
        return jsonify({"success": False, "error": str(e)})
    finally:
        conn.close()

# === Delete WiFi ===
@app.route("/delete/<int:id>", methods=["POST"])
def delete_wifi(id):
    conn = get_localdb_connection()
    cur = conn.cursor()
    try:
        cur.execute("DELETE FROM wifi_passwords WHERE id = %s", (id,))
        conn.commit()
        return jsonify({"success": True})
    except Exception as e:
        logger.error(f"Delete error: {e}")
        return jsonify({"success": False, "error": str(e)})
    finally:
        conn.close()

# === Start Server ===
if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5555, debug=True)
