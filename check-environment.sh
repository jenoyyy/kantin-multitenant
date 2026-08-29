#!/bin/bash
echo "=== Cek Environment Kantin Multi-Tenant ==="

check() {
  if command -v "$1" &> /dev/null || command -v "$1.cmd" &> /dev/null; then
    echo "OK: $1 terpasang"
  else
    echo "GAGAL: $1 tidak ditemukan"
    exit 1
  fi
}

check php
check composer
check npm
check git

echo "=== Semua tool utama OK ==="