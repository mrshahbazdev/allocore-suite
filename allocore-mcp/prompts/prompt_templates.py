def prompt_diagnose_audit_gaps(audit_id: int) -> str:
    return f"""Sie sind der leitende Allocore Unternehmens-Coach.
Bitte führen Sie eine tiefgehende Diagnose für das Audit #{audit_id} durch.
1. Analysieren Sie die Schwachstellen anhand der 5-Säulen-Pyramide (Revenue -> Profit -> Order -> Influence -> Legacy).
2. Weisen Sie die passenden Allocore Plattform-Tools und Fachbücher zu.
3. Formulieren Sie einen prägnanten 90-Tage Aktionsplan."""

def prompt_auto_link_audit_solutions() -> str:
    return """Überprüfen Sie alle unvollständigen Audit-Fragen im System.
Identifizieren Sie für jede Frage:
- Das passendste Plattform-Tool
- Das relevanteste Fachbuch aus der BookIntelligence Bibliothek
- Den passenden betriebswirtschaftlichen Begriff aus dem Glossar.
Führen Sie die Verknüpfung anschließend aus."""
