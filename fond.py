from flask import Flask, request, jsonify
import os
import json
from datetime import datetime

app = Flask(__name__)

# Path to store the previous values
DATA_FILE = os.path.join(os.path.dirname(__file__), 'kpi_data.json')

# Initialize data file if it doesn't exist
if not os.path.exists(DATA_FILE):
    with open(DATA_FILE, 'w') as f:
        json.dump({"fonds_propre": []}, f)

@app.route('/previous-fonds-propre', methods=['GET'])
def get_previous_fonds_propre():
    try:
        with open(DATA_FILE, 'r') as f:
            data = json.load(f)
        
        last_value = data['fonds_propre'][-1] if data['fonds_propre'] else None
        return jsonify(last_value)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/store-fonds-propre', methods=['POST'])
def store_fonds_propre():
    try:
        with open(DATA_FILE, 'r') as f:
            data = json.load(f)

        new_data = request.get_json()
        value = round(new_data.get('value'), 2)
        date = new_data.get('date', datetime.now().isoformat())

        # Check if last value is the same (rounded)
        if data['fonds_propre']:
            last_value = round(data['fonds_propre'][-1]['value'], 2)
            if abs(last_value - value) < 1:
                return jsonify({"skipped": "Duplicate or too similar"})

        # Keep only the last 12 entries
        if len(data['fonds_propre']) >= 12:
            data['fonds_propre'].pop(0)

        data['fonds_propre'].append({"value": value, "date": date})

        with open(DATA_FILE, 'w') as f:
            json.dump(data, f, indent=2)

        return jsonify({"success": True})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)