# Approvio

**Approvio** ist ein webbasiertes, quelloffenes Antragssystem für Fördervereine – entwickelt mit dem Ziel, die Bearbeitung von Förderanträgen transparenter, strukturierter und kollaborativer zu gestalten.

## ✨ Überblick

In vielen Fördervereinen – insbesondere an Schulen – fehlt es an einfachen digitalen Werkzeugen, um Anträge auf Fördermittel effizient entgegenzunehmen, gemeinsam zu bewerten und transparent zu entscheiden. Approvio schließt diese Lücke:

- Antragsteller können ohne Registrierung Förderanträge einreichen und Unterlagen hochladen.
- Der Vorstand wird automatisch über neue Anträge informiert.
- Vorstandsmitglieder können Anträge online kommentieren und bewerten.
- Sobald eine Mehrheitsentscheidung vorliegt, wird der Antragsteller benachrichtigt.
- Der gesamte Entscheidungsprozess ist nachvollziehbar und sicher dokumentiert.

Approvio ist für kleine bis mittlere Fördervereine gedacht – insbesondere im Bildungs- und Kulturbereich – und unterstützt ehrenamtliche Strukturen mit einer intuitiven, datenschutzfreundlichen Lösung.

---

## 🎯 Zielgruppen

- Schul-Fördervereine
- Elterninitiativen
- Gemeinnützige Organisationen mit Förderbudget
- Kleine Stiftungen oder Kulturvereine
- Ehrenamtliche Vorstände, die digital zusammenarbeiten wollen

---

## 💡 Projektidee

Viele Vereine arbeiten noch mit analogen Formularen, E-Mail-Verläufen und mündlicher Kommunikation. Das führt oft zu:

- mangelnder Transparenz im Entscheidungsprozess,
- unklaren Fristen und Verantwortlichkeiten,
- fehlender Archivierung oder Dokumentation,
- und damit zu Verzögerungen und Unzufriedenheit.

**Approvio** möchte eine moderne, niedrigschwellige Alternative bieten – einfach zu hosten, datenschutzkonform und erweiterbar.

---

## 🧩 Funktionsüberblick (geplant / in Entwicklung)

| Bereich              | Funktion                                              | Status     |
|----------------------|--------------------------------------------------------|------------|
| Antragstellung       | Öffentliche Antragseinreichung ohne Login             | 🟢 geplant |
| Dateiupload          | Anhänge wie PDFs oder Bilder hochladen                | 🟢 geplant |
| Benachrichtigung     | E-Mail-Benachrichtigung an Vorstandsmitglieder        | 🟢 geplant |
| Entscheidungsprozess | Kommentieren, Zustimmen, Ablehnen                     | 🟢 geplant |
| Mehrheitslogik       | Automatische Entscheidung bei erreichtem Quorum       | 🟢 geplant |
| Rückmeldung          | Automatische E-Mail an Antragsteller                  | 🟢 geplant |
| Archivierung         | Chronologisches Antragarchiv mit Filtermöglichkeit    | ⚪ optional |
| Rollenverwaltung     | Unterschiedliche Berechtigungen (Vorstand, Admin etc) | ⚪ optional |
| Export               | Antragsübersicht als PDF oder CSV                     | ⚪ optional |

---

## 🚀 Technischer Rahmen (vorgesehen)

- **Frontend:** Vue.js oder React
- **Backend:** Node.js mit Express oder Python (FastAPI)
- **Datenbank:** PostgreSQL oder SQLite (für einfache Selbst-Hosting-Option)
- **E-Mail:** SMTP-basierte Benachrichtigungen (z. B. via Mailjet, Mailgun)
- **Hosting:** Selbsthostbar auf z. B. Uberspace, Netcup, Hetzner, Docker etc.

Ziel ist eine **einfach zu betreibende Anwendung**, die mit wenigen Konfigurationsschritten lauffähig ist – ideal für Ehrenamtliche ohne große IT-Erfahrung.

---

## 📦 Installation & Setup

> ⚠️ Noch in Entwicklung – Setup-Skripte und Dockerfile folgen.

---

## 🛡️ Lizenz

Dieses Projekt („Approvio“) ist unter der [GNU General Public License Version 3 (GPLv3)](https://www.gnu.org/licenses/gpl-3.0.de.html) veröffentlicht.

### ⚠️ **Einschränkung zur kommerziellen Nutzung**

Gemäß Abschnitt 7 der GPLv3 enthält diese Lizenz eine Zusatzklausel:

> **Jegliche kommerzielle Nutzung ist untersagt.**

Die Nutzung, Weitergabe oder Weiterentwicklung dieses Projekts ist ausschließlich für **nicht-kommerzielle Zwecke** gestattet. Kommerzielle Verwendung – z. B. Integration in kostenpflichtige Produkte, Hosting als Dienstleistung gegen Entgelt oder jede Form der Monetarisierung – ist **nur mit ausdrücklicher schriftlicher Genehmigung der Urheber** erlaubt.

Für Anfragen zur kommerziellen Lizenzierung wende dich bitte an:

📧 **author@approvio.de**

---

## 👥 Mitmachen

Pull Requests, Verbesserungsvorschläge, Fehlerberichte und Ideen sind herzlich willkommen!

---

## 📚 Zukunftsideen

- OAuth-Integration für Authentifizierung mit Schul-Accounts (z. B. Microsoft 365, IServ)
- Statistiken und Fördervolumen-Auswertung
- Übersetzungen / Mehrsprachigkeit

---

## ❤️ Unterstützen

Wenn du das Projekt gut findest oder es in deinem Verein einsetzen möchtest:  
⭐️ Sternchen geben, teilen, Feedback geben – oder ein Issue öffnen!  
Danke für dein Interesse an **Approvio**.

