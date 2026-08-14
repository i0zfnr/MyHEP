"""Synchronize literal Laravel translation calls into the EN/MS JSON catalogues.

The script never rewrites Blade or PHP source. Existing translations always win.
New English source phrases remain English in the EN catalogue and new Malay source
phrases remain Malay in the MS catalogue. Cross-language values are derived from
the existing reviewed catalogues and a small project terminology glossary.
"""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE_DIRS = [ROOT / "resources" / "views", ROOT / "app", ROOT / "routes"]
# Capture the first literal argument even when __() also receives replacements.
CALL = re.compile(r"__\(\s*(['\"])((?:\\.|(?!\1).)*)\1", re.DOTALL)
# Laravel grouped catalogue keys look like ``file.section.key``. Do not treat
# ordinary UI copy containing a period (for example ``Generating...`` or
# ``No. Bilik``) as a grouped key.
GROUPED_KEY = re.compile(r"^[a-z][a-z0-9_-]*\.[a-z0-9_.-]+$")

MALAY_MARKERS = {
    "akaun", "alamat", "anda", "baharu", "belum", "biasiswa", "bilangan",
    "borang", "bukti", "butiran", "carian", "cari", "catatan", "dalam",
    "denda", "dihantar", "dokumen", "gambar", "hantar", "jumlah", "kembali",
    "kata", "kemaskini", "kesalahan", "maklumat", "matrik", "muat", "nama",
    "pautan", "pelajar", "pemohon", "penaja", "pengajian", "penjaga",
    "pengumuman", "permohonan", "profil", "rekod", "semak", "senarai",
    "sila", "status", "tarikh", "telefon", "tempat", "tiada", "tindakan",
}

EN_TO_MS = {
    "Action": "Tindakan", "Active": "Aktif", "All": "Semua",
    "All groups": "Semua kumpulan", "All statuses": "Semua status",
    "Amount": "Jumlah", "Back": "Kembali", "Cancel": "Batal",
    "Category": "Kategori", "Confirmed": "Disahkan", "Create": "Cipta",
    "Delete": "Padam", "Download": "Muat Turun", "Edit": "Edit",
    "Email": "E-mel", "Filter": "Tapis", "History": "Sejarah",
    "Name": "Nama", "No": "Tidak", "Not specified": "Tidak dinyatakan",
    "Pending": "Menunggu", "Program": "Program", "Reset": "Set Semula",
    "Save": "Simpan", "Search": "Cari", "Status": "Status",
    "Student": "Pelajar", "Students": "Pelajar", "Submit": "Hantar",
    "Total": "Jumlah", "View": "Lihat", "Yes": "Ya",
    "Academic Session": "Sesi Akademik", "Account information": "Maklumat akaun",
    "Admin Profile": "Profil Admin", "Advanced filters": "Penapis lanjutan",
    "All programs": "Semua program", "Attendance": "Kehadiran",
    "Attendance Only": "Kehadiran Sahaja", "Back to Dashboard": "Kembali ke Papan Pemuka",
    "Back to Details": "Kembali ke Butiran", "Background / description": "Latar belakang / penerangan",
    "B40 Students": "Pelajar B40", "Certificates": "Sijil",
    "Change password": "Tukar kata laluan", "Confirmed Amount": "Jumlah Disahkan",
    "Current status": "Status semasa", "Dashboard": "Papan Pemuka",
    "Family Income": "Pendapatan Keluarga", "Household Income": "Pendapatan Isi Rumah",
    "New chat": "Sembang baharu", "No welfare records found": "Tiada rekod kebajikan ditemui",
    "Overview of the discipline and scholarship modules.": "Gambaran keseluruhan modul disiplin dan biasiswa.",
    "Overview of guard house monitoring and student movement.": "Gambaran keseluruhan pemantauan pondok pengawal dan pergerakan pelajar.",
    "Overview of the student discipline module.": "Gambaran keseluruhan modul disiplin pelajar.",
    "Overview of the student scholarship module.": "Gambaran keseluruhan modul biasiswa pelajar.",
    "This account has no module access.": "Akaun ini tidak mempunyai akses modul.",
    "OKU Students": "Pelajar OKU", "Profile photo": "Gambar profil",
    "Questionnaire": "Soal Selidik", "Report": "Laporan",
    "Scholarship": "Biasiswa", "Scholarship Status": "Status Biasiswa",
    "Student Details": "Butiran Pelajar", "Total Records": "Jumlah Rekod",
    "View Student": "Lihat Pelajar", "Welfare Group": "Kumpulan Kebajikan",
    "WELFARE RECORDS": "REKOD KEBAJIKAN",
    "Welfare assistance records will appear here.": "Rekod bantuan kebajikan akan dipaparkan di sini.",
    "Search name, matric number or program": "Cari nama, nombor matrik atau program",
    "Track students receiving welfare assistance and review their B40, OKU, guardian and household information.": "Jejaki pelajar yang menerima bantuan kebajikan dan semak maklumat B40, OKU, penjaga serta isi rumah mereka.",
}

REVIEWED_MS = {
    "Account administration": "Pentadbiran Akaun",
    "Account status": "Status akaun",
    "Active Scholarships": "Biasiswa Aktif",
    "Active checkouts without return": "Daftar keluar aktif yang belum dipulangkan",
    "Active now": "Aktif sekarang",
    "Admins & staff": "Admin dan kakitangan",
    "Add Scholarship Record": "Tambah Rekod Biasiswa",
    "Admin Dashboard": "Papan Pemuka Admin",
    "Back to Dashboard": "Kembali ke Papan Pemuka",
    "CPU Usage": "Penggunaan CPU",
    "Check-Outs Today": "Daftar Keluar Hari Ini",
    "Close import window": "Tutup tetingkap import",
    "Current processing load": "Beban pemprosesan semasa",
    "Checkpoint": "Pusat pemeriksaan",
    "Choose a curated color mood for StudentEdge. Status and safety colors stay unchanged.": "Pilih suasana warna yang disediakan untuk StudentEdge. Warna status dan keselamatan tidak berubah.",
    "Department / Unit": "Jabatan / Unit",
    "Delete scholarship record": "Padam rekod biasiswa",
    "Delete this scholarship record?": "Padam rekod biasiswa ini?",
    "Disk Usage": "Penggunaan Cakera",
    "Import Staff": "Import Kakitangan",
    "Export CSV": "Eksport CSV",
    "Edit Scholarship Announcement": "Edit Pengumuman Biasiswa",
    "IC No.": "No. IC",
    "Live Out": "Tinggal di luar kampus",
    "Live Out / Luar Kampus": "Tinggal di luar kampus",
    "Install on Android": "Pasang pada Android",
    "Last seen": "Kali terakhir dilihat",
    "Memory Usage": "Penggunaan Memori",
    "Latest Scholarship Announcements": "Pengumuman Biasiswa Terkini",
    "Latest Scholarship Records": "Rekod Biasiswa Terkini",
    "My Scholarship Records": "Rekod Biasiswa Saya",
    "No Matrik": "Nombor Matrik",
    "No authenticated visitors are currently active.": "Tiada pelawat yang telah disahkan sedang aktif.",
    "Overall Load": "Beban Keseluruhan",
    "Papan Pemuka": "Papan Pemuka",
    "No scholarship announcements.": "Tiada pengumuman biasiswa.",
    "No scholarship records yet.": "Belum ada rekod biasiswa.",
    "No scholarship records.": "Tiada rekod biasiswa.",
    "Open Scholarship": "Buka Biasiswa",
    "Print QR Labels": "Cetak Label QR",
    "Pending Applications": "Permohonan Menunggu",
    "Pending Payment Applications": "Permohonan Bayaran Menunggu",
    "Pending Sticker Applications": "Permohonan Pelekat Menunggu",
    "Scan a Laptop QR": "Imbas QR Komputer Riba",
    "Search and manage account access from one place.": "Cari dan urus akses akaun dari satu tempat.",
    "Scholarship Announcement Management": "Pengurusan Pengumuman Biasiswa",
    "Scholarship Announcements": "Pengumuman Biasiswa",
    "Scholarship Module": "Modul Biasiswa",
    "Scholarship Records": "Rekod Biasiswa",
    "Staff Dashboard": "Papan Pemuka Kakitangan",
    "Staff category": "Kategori kakitangan",
    "System Monitoring": "Pemantauan Sistem",
    "System Performance": "Prestasi Sistem",
    "Student Dashboard": "Papan Pemuka Pelajar",
    "Student account summary for scholarship and discipline modules.": "Ringkasan akaun pelajar untuk modul biasiswa dan disiplin.",
    "Reset": "Set Semula",
    "Reset Password": "Set Semula Kata Laluan",
    "Pending Fine Applications": "Permohonan Denda Menunggu",
    "Pending Records": "Rekod Menunggu",
    "Returned past the allowance": "Pulang melebihi tempoh yang dibenarkan",
    "StudentEdge AI": "AI StudentEdge",
    "Total Scholarship Records": "Jumlah Rekod Biasiswa",
    "Unpaid Offenses": "Kesalahan Belum Dibayar",
    "Edit Rekod Scholarship": "Edit Rekod Biasiswa",
    "Jumlah Rekod Scholarship": "Jumlah Rekod Biasiswa",
    "Pengumuman Scholarship Terkini": "Pengumuman Biasiswa Terkini",
    "Pengurusan Pengumuman Scholarship": "Pengurusan Pengumuman Biasiswa",
    "Pengurusan Rekod Scholarship": "Pengurusan Rekod Biasiswa",
    "Rekod Scholarship Saya": "Rekod Biasiswa Saya",
    "Rekod Scholarship Terkini": "Rekod Biasiswa Terkini",
    "Tambah Pengumuman Scholarship": "Tambah Pengumuman Biasiswa",
    "Tambah Rekod Scholarship": "Tambah Rekod Biasiswa",
    "Tiada data SCHOLARSHIP B40 TVET.": "Tiada data Biasiswa B40 TVET.",
    "Tiada pengumuman scholarship.": "Tiada pengumuman biasiswa.",
    "Tiada rekod scholarship.": "Tiada rekod biasiswa.",
    "Upload the official staff workbook or a structured CSV file.": "Muat naik buku kerja kakitangan rasmi atau fail CSV berstruktur.",
}

REVIEWED_EN = {
    "Akses modul untuk akaun ini belum dikonfigurasi.": "Module access has not been configured for this account.",
    "Cari nama/matrik/IC/program": "Search name/matric number/IC/program",
    "Cari nama/matrik/penyedia": "Search name/matric number/provider",
    "Edit Rekod Scholarship": "Edit Scholarship Record",
    "Jabatan Hal Ehwal Pelajar": "Student Affairs Department",
    "Jumlah Peserta": "Total Participants",
    "Maklumat Rekod": "Record Information",
    "NOTIS DENDA KESALAHAN TATATERTIB": "DISCIPLINARY OFFENCE FINE NOTICE",
    "Nama Pelajar": "Student Name",
    "Nama Program": "Program Name",
    "Nama:": "Name:",
    "No Matrik": "Matric Number",
    "Pengarah Program": "Program Director",
    "Pengurusan Rekod Scholarship": "Scholarship Record Management",
    "Rekod Belum Disahkan": "Unverified Records",
    "Status OKU": "Disability Status",
    "Telefon": "Phone Number",
    "Tiada data SCHOLARSHIP B40 TVET.": "No B40 TVET scholarship data.",
    "Tiada Akses Modul": "No Module Access",
    "Tiada resit bayaran terbaru.": "No recent payment receipt.",
}


def load_json(path: Path) -> dict[str, str]:
    return json.loads(path.read_text(encoding="utf-8-sig"))


def looks_malay(text: str) -> bool:
    words = set(re.findall(r"[A-Za-zÀ-ÿ]+", text.lower()))
    return len(words & MALAY_MARKERS) >= 1


def extract_keys() -> set[str]:
    keys: set[str] = set()
    for directory in SOURCE_DIRS:
        for path in directory.rglob("*.php"):
            content = path.read_text(encoding="utf-8", errors="ignore")
            for match in CALL.finditer(content):
                key = match.group(2)
                if "$" not in key and "\\" not in key and not GROUPED_KEY.match(key):
                    keys.add(key)
    return keys


def main() -> None:
    en_path, ms_path = ROOT / "lang" / "en.json", ROOT / "lang" / "ms.json"
    en, ms = load_json(en_path), load_json(ms_path)
    keys = extract_keys()

    # Existing reviewed cross-language values are the primary translation memory.
    known_en_to_ms = {key: value for key, value in ms.items() if value != key}
    known_en_to_ms.update(EN_TO_MS)
    known_ms_to_en = {key: value for key, value in en.items() if value != key}

    unresolved_ms: list[str] = []
    unresolved_en: list[str] = []
    for key in sorted(keys):
        if key not in en:
            if looks_malay(key):
                en[key] = known_ms_to_en.get(key, key)
                if en[key] == key:
                    unresolved_en.append(key)
            else:
                en[key] = key

        if key not in ms:
            if looks_malay(key):
                ms[key] = key
            else:
                ms[key] = known_en_to_ms.get(key, key)
                if ms[key] == key:
                    unresolved_ms.append(key)

    # Project terminology and human-reviewed corrections always win.
    ms.update(REVIEWED_MS)
    en.update(REVIEWED_EN)

    en_path.write_text(json.dumps(dict(sorted(en.items())), ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    ms_path.write_text(json.dumps(dict(sorted(ms.items())), ensure_ascii=False, indent=4) + "\n", encoding="utf-8")

    report = ROOT / ".tmp" / "translation-review-required.json"
    report.parent.mkdir(exist_ok=True)
    report.write_text(json.dumps({"ms": unresolved_ms, "en": unresolved_en}, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Synchronized {len(keys)} literal keys. Manual review: ms={len(unresolved_ms)}, en={len(unresolved_en)}")


if __name__ == "__main__":
    main()
