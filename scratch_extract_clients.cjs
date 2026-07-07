const fs = require('fs');

const legacyHtml = fs.readFileSync('/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/legacy-html/clients-list.html', 'utf8');

// Extract the layout section
const sectionRegex = /<div class="page-container">[\s\S]*?(?=<\/body>)/;
const match = legacyHtml.match(sectionRegex);

if (match) {
    let content = match[0];
    
    // Replace hardcoded clientsData with Astro prop
    content = content.replace(/const clientsData = \[.*?\];/s, "const clientsData = window.__ASTRO_CLIENTS_LIST__ || [];");
    
    // Create the Astro component
    const astroComponent = `---
import { getClients } from '../lib/wordpress';
const clientsAll = await getClients();

// Map WP Client data to the format the JS expects
const mappedClients = clientsAll.map((client: any) => ({
    client: client.title?.rendered || "",
    esc_region: client.meta?.region === "north" ? 10 : 
                client.meta?.region === "south" ? 1 :
                client.meta?.region === "east" ? 7 :
                client.meta?.region === "west" ? 18 :
                client.meta?.region === "central" ? 13 :
                client.meta?.region === "panhandle" ? 16 :
                client.meta?.region === "hill-country" ? 20 :
                client.meta?.region === "southeast" ? 4 : 
                client.meta?.esc_region || 13 // fallback
}));
---
<style>
/* Sidebar Filters */
.filter-sidebar {
    width: 300px;
    flex-shrink: 0;
    background: var(--color-bg-white, #ffffff);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    height: calc(100vh - 60px);
    position: sticky;
    top: 30px;
    display: flex;
    flex-direction: column;
}

.filter__header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e2e8f0;
}

.filter__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary-dark, #0056b3);
}

.filter__subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 5px;
}

.filter__list {
    overflow-y: auto;
    flex-grow: 1;
    padding-right: 10px;
}

.filter__list::-webkit-scrollbar { width: 6px; }
.filter__list::-webkit-scrollbar-track { background: transparent; }
.filter__list::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

.esc-filter {
    display: flex;
    align-items: center;
    padding: 12px 10px;
    margin-bottom: 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.esc-filter:hover { background-color: #f1f5f9; }
.esc-filter__input { display: none; }
.esc-filter__label { font-size: 1.1rem; font-weight: 400; color: var(--color-text-main, #1e293b); user-select: none; transition: all 0.2s; }
.esc-filter__input:checked ~ .esc-filter__label { font-weight: 700; color: var(--color-primary-green, #16a34a); }

/* Client Grid */
.clients-main { flex-grow: 1; }
.clients__header {
    margin-bottom: 24px;
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    border-bottom: 2px solid var(--color-primary-green, #16a34a);
}

.header__title-area {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
    text-align: left;
}

.header-logo { height: 60px; display: block; }
.clients__title { font-size: 2rem; font-weight: 700; color: var(--color-primary-dark, #11411d); margin: 0; line-height: 1; }
.print-key { display: none; font-size: 10pt; font-weight: 700; color: var(--color-primary-green, #16a34a); text-align: left; margin-top: 4px; }
.clients__count { font-size: 1rem; color: #64748b; font-weight: 600; background: #ffffff; padding: 8px 16px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
.clients-grid { column-count: 6; column-gap: 10px; }

/* Client Cards */
.client-card { padding: 2px 4px; display: flex; flex-direction: column; justify-content: center; transition: all 0.2s; break-inside: avoid; page-break-inside: avoid; margin-bottom: 6px; }
.client-card:hover { opacity: 0.7; }
.client-card__name { font-size: 0.75rem; font-weight: 400; line-height: 1.2; color: var(--color-text-main, #1e293b); transition: all 0.2s; }

/* Interaction States */
.clients-grid.has-active-filters .client-card.highlighted .client-card__name { font-weight: 700; color: var(--color-primary-green, #16a34a); }

/* Responsive */
@media screen and (max-width: 1400px) { .clients-grid { column-count: 5; } }
@media screen and (max-width: 1100px) { .clients-grid { column-count: 4; } .filter-sidebar { width: 250px; } }
@media screen and (max-width: 800px) {
    .page-container { flex-direction: column; }
    .filter-sidebar { width: 100%; height: auto; position: relative; top: 0; }
    .filter__list { display: flex; flex-wrap: wrap; gap: 10px; }
    .esc-filter { width: calc(33.333% - 10px); margin-bottom: 0; }
}

@media print {
    .page-container { display: block !important; padding: 0 !important; margin: 0 !important; max-width: none !important; }
    .filter-sidebar { display: none !important; }
    .clients-main { display: block !important; width: 100% !important; }
    .clients-grid { column-count: 6 !important; column-gap: 12px !important; }
    .client-card { margin-bottom: 4px !important; padding: 0 !important; }
    .client-card__name { font-size: 8pt !important; line-height: 1.3 !important; }
    .clients__header { display: flex !important; margin-bottom: 20px !important; flex-direction: row !important; justify-content: flex-start !important; align-items: center !important; border-bottom: 2px solid #16a34a !important; }
    .header__title-area { display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: flex-start !important; gap: 20px !important; }
    .header-logo { height: 60px !important; margin-bottom: 0 !important; }
    .clients__title { font-size: 16pt !important; color: #11411d !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .print-key { display: block !important; color: #16a34a !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<script define:vars={{ mappedClients }}>
    window.__ASTRO_CLIENTS_LIST__ = mappedClients;
</script>

${content}
`;

    fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/src/components/ClientsList.astro', astroComponent);
    console.log("ClientsList.astro created successfully.");
} else {
    console.log("Could not find section.");
}
