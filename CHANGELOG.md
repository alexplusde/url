# Changelog

## 10.0.0-dev-2026-11-22

- fix(sitemap): Profil-URLs werden nach der Domain der Sitemap-Anfrage (Host) gefiltert, je URL in ihrer Sprache, statt nach der Domain des aktuellen Artikels; bei gemeinsam genutztem 404- oder Startartikel listete die Sitemap von Domain A sonst die Profile von Domain B und ließ die eigenen aus; bei unbekanntem Host bleibt die Sitemap ohne Profil-URLs (#50)
