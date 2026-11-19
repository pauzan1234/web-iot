#include <WiFi.h>
#include <HTTPClient.h>

// ===== KONFIGURASI WIFI =====
const char* ssid     = "vivo Y17s";
const char* password = "ayesha123";

// ===== KONFIGURASI SERVER =====
// Gunakan IP komputer/server (bukan localhost), contoh: 192.168.1.10
const char* serverUrl = "http://10.192.163.78/web-iot/simpan_data.php";
const char* apiKey    = "sgtrahasia"; // sama dengan di simpan_data.php

// ===== PIN HC-SR04 =====
// PENTING: pin ECHO mengeluarkan 5V, ESP32 hanya toleran 3.3V.
// Gunakan pembagi tegangan (misal 1k + 2k ohm) pada pin ECHO.
const int TRIG_PIN = 5;
const int ECHO_PIN = 18;

// Interval kirim data (ms)
const unsigned long INTERVAL = 3000;
unsigned long lastSend = 0;

// Baca jarak (cm), rata-rata dari 5 kali pembacaan
float bacaJarak() {
  float total = 0;
  int valid = 0;

  for (int i = 0; i < 5; i++) {
    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);

    long durasi = pulseIn(ECHO_PIN, HIGH, 30000); // timeout 30 ms
    if (durasi > 0) {
      total += durasi * 0.0343 / 2.0;
      valid++;
    }
    delay(50);
  }

  if (valid == 0) return -1; // tidak ada pembacaan valid
  return total / valid;
}

void connectWiFi() {
  Serial.print("Menghubungkan ke WiFi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int percobaan = 0;
  while (WiFi.status() != WL_CONNECTED && percobaan < 40) {
    delay(500);
    Serial.print(".");
    percobaan++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi terhubung");
    Serial.print("IP ESP32: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nGagal terhubung WiFi");
  }
}

void kirimData(float jarak) {
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String body = "api_key=" + String(apiKey) + "&jarak=" + String(jarak, 1);

  int kode = http.POST(body);

  if (kode > 0) {
    Serial.printf("HTTP %d -> %s\n", kode, http.getString().c_str());
  } else {
    Serial.printf("Gagal kirim: %s\n", http.errorToString(kode).c_str());
  }

  http.end();
}

void setup() {
  Serial.begin(115200);
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  connectWiFi();
}

void loop() {
  // Reconnect otomatis jika WiFi putus
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
    return;
  }

  if (millis() - lastSend >= INTERVAL) {
    lastSend = millis();

    float jarak = bacaJarak();

    if (jarak < 0) {
      Serial.println("Sensor tidak terbaca, data tidak dikirim");
    } else {
      Serial.printf("Jarak: %.1f cm\n", jarak);
      kirimData(jarak);
    }
  }
}
