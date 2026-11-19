
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// KONFIGURASI WIFI
const char* ssid = "vivo Y17s";
const char* password = "ayesha123";

// URL API PHP
const char* serverURL = "http://10.192.163.78/web-iot/api_status.php";

// PIN LED
const int ledPin = 2;

// Interval pembacaan database
unsigned long previousMillis = 0;
const long interval = 1000;

void setup() {
    Serial.begin(115200);

    // Konfigurasi LED
    pinMode(ledPin, OUTPUT);
    digitalWrite(ledPin, LOW);

    // Koneksi WiFi
    WiFi.begin(ssid, password);

    Serial.print("Menghubungkan WiFi");

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }

    Serial.println();
    Serial.println("WiFi Terhubung!");
    Serial.print("IP ESP32: ");
    Serial.println(WiFi.localIP());
}

void loop() {

    unsigned long currentMillis = millis();

    if (currentMillis - previousMillis >= interval) {

        previousMillis = currentMillis;

        if (WiFi.status() == WL_CONNECTED) {

            HTTPClient http;

            http.begin(serverURL);

            int httpCode = http.GET();

            if (httpCode == HTTP_CODE_OK) {

                String payload = http.getString();

                Serial.println("Data dari Server:");
                Serial.println(payload);

                // Membaca JSON
                JsonDocument doc;

                DeserializationError error =
                    deserializeJson(doc, payload);

                if (!error) {

                    int status = doc["status"];

                    Serial.print("Status Lampu: ");
                    Serial.println(status);

                    if (status == 1) {

                        digitalWrite(ledPin, HIGH);

                        Serial.println("LED MENYALA");

                    } else {

                        digitalWrite(ledPin, LOW);

                        Serial.println("LED MATI");
                    }

                } else {
                    Serial.println("Gagal membaca JSON");
                }

            } else {

                Serial.print("HTTP Error: ");
                Serial.println(httpCode);
            }

            http.end();

        } else {

            Serial.println("WiFi Terputus!");
        }
    }
}
