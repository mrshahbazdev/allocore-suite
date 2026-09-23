def prompt_diagnose_audit_gaps(audit_id: int) -> str:
    return f"""Sie sind der leitende Allocore Unternehmens-Coach.
Bitte führen Sie eine tiefgehende Diagnose für das Audit #{audit_id} durch.
1. Analysieren Sie die Schwachstellen anhand der 5-Säulen-Pyramide (Revenue -> Profit -> Order -> Influence -> Legacy).
2. Weisen Sie die passenden Allocore Plattform-Tools und Fachbücher zu.
3. Formulieren Sie einen prägnanten 90-Tage Aktionsplan."""

def prompt_executive_audit_briefing(audit_id: int) -> str:
    return f"""Erstellen Sie ein C-Level Vorstandsbriefing für Audit #{audit_id}. Formulieren Sie strategische Kernaussagen zu finanziellen Risiken, Engpässen und Quick Wins."""

def prompt_seo_content_creator(topic: str, book_id: int = 451) -> str:
    return f"""Erstellen Sie einen suchmaschinenoptimierten, 8-teiligen Fachartikel zum Thema '{topic}'. Binden Sie passende Allocore-Tools sowie die Buchempfehlung Box (Buch ID #{book_id}) nahtlos ein."""

def prompt_internal_seo_optimizer(post_id: int = 1) -> str:
    return f"""Prüfen Sie den Fachartikel #{post_id} auf Vorkommen relevanter Allocore-Glossarbegriffe und generieren Sie kontextuelle Querverweise."""

def prompt_lead_nurture_strategy(lead_id: int) -> str:
    return f"""Analysieren Sie das Profil und die Interaktionen von Lead #{lead_id} und entwickeln Sie eine maßgeschneiderte B2B-Ansprachestrategie mit ROI-Fokus."""

def prompt_customer_support_resolver(ticket_id: int) -> str:
    return f"""Beantworten Sie Support-Ticket #{ticket_id} lösungsorientiert und professionell. Verweisen Sie auf passende Allocore-Tools und Wissensartikel."""

def prompt_kpi_cockpit_analyzer(company_name: str) -> str:
    return f"""Untersuchen Sie die Kennzahlenlandschaft von '{company_name}'. Identifizieren Sie kritische Frühwarnindikatoren (Runway, Deckungsbeitrag, CAC, CLV) und schlagen Sie die passende Allocore-Modulkombination vor."""

def prompt_sop_generator(process_name: str, role: str = "Betriebsleiter") -> str:
    return f"""Erstellen Sie eine präzise, fehlertolerante Standard Operating Procedure (SOP) für den Prozess '{process_name}' (Verantwortlich: {role}). Gliedern Sie in Vorbedingungen, Einzelschritte, Qualitätskontrolle und Stellvertreter-Regelungen."""

def prompt_case_study_writer(client_name: str, industry: str) -> str:
    return f"""Verfassen Sie eine überzeugende Erfolgsgeschichte (Case Study) für '{client_name}' aus der Branche '{industry}'. Strukturieren Sie nach Herausforderung, Lösung mit Allocore, messbaren ROI-Ergebnissen und Zitat."""

def prompt_auto_link_audit_solutions() -> str:
    return """Überprüfen Sie alle unvollständigen Audit-Fragen im System.
Identifizieren Sie für jede Frage:
- Das passendste Plattform-Tool
- Das relevanteste Fachbuch aus der BookIntelligence Bibliothek
- Den passenden betriebswirtschaftlichen Begriff aus dem Glossar.
Führen Sie die Verknüpfung anschließend aus."""

