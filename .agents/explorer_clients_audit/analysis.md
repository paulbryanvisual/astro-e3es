# Gap Analysis: Live Site vs Local Headless Astro Site

## 1. Overview Summary

This report performs a detailed comparison audit between the client posts published on the live site (using `clients_dump.json` as the live data representation) and the local headless Astro / WordPress environment.

* **Total Slugs Cataloged**: 108
* **Clients in Live Dump**: 100
* **Clients in Local WordPress**: 107
  * **Published Locally**: 27
  * **Drafts Locally**: 80
* **Featured Image Status**:
  * **Mapped with Real Images**: 27
  * **Using Taj Mahal Placeholder**: 80
* **Layout Structure Status**:
  * **Using custom Project block (`e3es/project`)**: 101
  * **Missing Project block entirely**: 6
* **Videos Found**: 6 pages contain embedded Vimeo/YouTube videos.

---

## 2. Key Action Items & Gaps

### A. Missing Clients (In Live Dump but NOT Published Locally)
There are **77 clients** that are published in the live dump but are either missing or in draft status in the local WordPress.
* **Missing from Local WordPress database completely (1)**:
  * `e3-general` (E3_General - Note: this is a general/mockup post and not a real client page).
* **In Local WordPress but in Draft status (76)**:
  These posts exist in the local WordPress database but are saved as **Drafts**. They must be updated to **Publish** to align with the live site:
  - `woodville-isd` (Woodville ISD)
  - `west-hardin-ccisd` (West Hardin CCISD)
  - `weslaco-isd` (Weslaco ISD)
  - `waxahachie-isd` (Waxahachie ISD)
  - `vernon-isd` (Vernon ISD)
  - `valley-view-isd` (Valley View ISD)
  - `trinity-isd` (Trinity ISD)
  - `trenton-isd` (Trenton ISD)
  - `tom-bean-isd` (Tom Bean ISD)
  - `texas-facilities-commission` (Texas Facilities Commission)
  - `somerset-isd` (Somerset ISD)
  - `skidmore-tynan-isd` (Skidmore-Tynan ISD)
  - `silsbee-isd` (Silsbee ISD)
  - `santa-fe-isd` (Santa Fe ISD)
  - `sanger-isd` (Sanger ISD)
  - `san-jacinto-community-college` (San Jacinto Community College)
  - `san-benito-cisd` (San Benito CISD)
  - `san-angelo-isd` (San Angelo ISD)
  - `saint-jo-isd` (Saint Jo ISD)
  - `rusk-isd` (Rusk ISD)
  - `roscoe-collegiate-isd` (Roscoe Collegiate ISD)
  - `robstown-isd` (Robstown ISD)
  - `poolville-isd` (Poolville ISD)
  - `pilot-point-isd` (Pilot Point ISD)
  - `pflugerville-isd` (Pflugerville ISD)
  - `pecos-isd` (Pecos ISD)
  - `odem-edroy-isd` (Odem-Edroy ISD)
  - `north-texas-medical-center` (North Texas Medical Center)
  - `normangee-isd` (Normangee ISD)
  - `nocona-isd` (Nocona ISD)
  - `new-boston-isd` (New Boston ISD)
  - `nacogdoches-isd` (Nacogdoches ISD)
  - `moulton-isd` (Moulton ISD)
  - `mesquite-isd` (Mesquite ISD)
  - `marble-falls-isd` (Marble Falls ISD)
  - `lyford-isd` (Lyford ISD)
  - `lufkin-isd` (Lufkin ISD)
  - `lubbock-isd` (Lubbock ISD)
  - `llano-isd` (Llano ISD)
  - `liberty-isd` (Liberty ISD)
  - `lancaster-isd` (Lancaster ISD)
  - `lamesa-isd` (Lamesa ISD)
  - `kennedale-isd` (Kennedale ISD)
  - `katy-isd` (Katy ISD)
  - `jasper-isd` (Jasper ISD)
  - `italy-isd` (Italy ISD)
  - `ingram-isd` (Ingram ISD)
  - `idea-public-schools` (IDEA Public Schools)
  - `highland-park-isd` (Highland Park ISD)
  - `hawkins-isd` (Hawkins ISD)
  - `hardin-jefferson-isd` (Hardin-Jefferson ISD)
  - `hardin-county` (Hardin County)
  - `gruver-isd` (Gruver ISD)
  - `galena-park-isd` (Galena Park ISD)
  - `gainesville-isd` (Gainesville ISD)
  - `ennis-isd` (Ennis ISD)
  - `edgewood-isd` (Edgewood ISD)
  - `eagle-pass-isd` (Eagle Pass ISD)
  - `desoto-isd` (DeSoto ISD)
  - `corsicana-isd` (Corsicana ISD)
  - `columbia-brazoria-isd` (Columbia-Brazoria ISD)
  - `cleveland-isd` (Cleveland ISD)
  - `chico-isd` (Chico ISD)
  - `cedar-hill-isd` (Cedar Hill ISD)
  - `castleberry-isd` (Castleberry ISD)
  - `caddo-mills-isd` (Caddo Mills ISD)
  - `brownsville-isd` (Brownsville ISD)
  - `brenham-isd` (Brenham ISD)
  - `bowie-isd` (Bowie ISD)
  - `big-sandy-isd` (Big Sandy ISD)
  - `bellevue-isd` (Bellevue ISD)
  - `banquete-isd` (Banquete ISD)
  - `ballinger-isd` (Ballinger ISD)
  - `baird-isd` (Baird ISD)
  - `anderson-shiro-cisd` (Anderson-Shiro CISD)
  - `bishop-cisd` (Bishop CISD)

### B. Extra Clients (In Local WordPress but NOT in Live Dump)
There are **8 clients** in the local WordPress database that are not present in the live dump.
* **Published Locally but must be removed (or are duplicates) (2)**:
  * `south-texas` (South Texas & Coast) - *Must be removed entirely as requested by the user.*
  * `gwh` (Goodall-Witcher Healthcare) - *This is a duplicate of `goodall-witcher-hospital`. The live site uses slug `goodall-witcher-hospital`. The `gwh` post should be trashed/removed.*
* **Published Locally (Legacy / Mapped but not in dump) (3)**:
  * `boyd-isd` (Boyd ISD) - *Legacy client, has real image mapping, published.*
  * `bryan-isd` (Bryan ISD) - *Legacy client, has real image mapping, published.*
  * `rio-hondo-isd` (Rio Hondo ISD) - *Legacy client, has real image mapping, published.*
* **Drafts Locally but not in dump (3)**:
  * `little-elm-isd` (Little Elm ISD)
  * `keene-isd` (Keene ISD)
  * `plano-isd` (Plano ISD)
  (Note: `city-of-stockdale` is a draft locally and is in the dump as `city-of-stockdale`? Wait, let's check!)

### C. Structure Audit (Project Block & Relationship Description)
The user wants the custom **E3 Project** (`e3es/project`) Gutenberg block to display project details under a short description of the client relationship.
* **Gutenberg Layout Structure Anomalies**:
  The following local posts **do NOT** contain the custom `e3es/project` Gutenberg block and need layout structure updates:
  - `south-texas` (South Texas & Coast) - Status: publish (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)
  - `donna-isd` (Donna ISD) - Status: publish (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)
  - `carrizo-springs-cisd` (Carrizo Springs CISD) - Status: publish (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)
  - `caldwell-isd` (Caldwell ISD) - Status: publish (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)
  - `gwh` (Goodall-Witcher Healthcare) - Status: publish (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)
  - `bryan-isd` (Bryan ISD) - Status: publish (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)
  
* **Missing Relationship Description**:
  The following posts have the Project block but do NOT have a short relationship description preceding it (going straight from intro-banner to project details):
  - `goodall-witcher-hospital` (Goodall Witcher Hospital - ID: 1459) - *Needs the relationship description paragraph from `gwh` added before the project block.*

### D. Media Audit (Placeholders & Video)
* **Clients still using the Taj Mahal Placeholder Image**:
  There are **80** client pages using the Taj Mahal placeholder image (`taj-mahal-placeholder.png`). These need their real featured images uploaded and mapped.
  
* **Client Pages with Video Integrations**:
  The following client pages contain Vimeo or YouTube videos:
  - `granbury-isd` (Granbury ISD) - Videos: https://player.vimeo.com/video/227283498?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479
  - `little-elm-isd` (Little Elm ISD) - Videos: https://player.vimeo.com/video/946653874?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479
  - `keene-isd` (Keene ISD) - Videos: https://player.vimeo.com/video/1176712805?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479
  - `plano-isd` (Plano ISD) - Videos: https://player.vimeo.com/video/1007829512?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479
  - `city-of-stockdale` (City of Stockdale) - Videos: https://player.vimeo.com/video/1171901749?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479
  - `boyd-isd` (Boyd ISD) - Videos: https://player.vimeo.com/video/1179578579?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479

---

## 3. Comprehensive Clients Audit Table

| Client Slug | Title | In Live Dump? | Local Status | Image Status | Has Project Block? | Description Before Project? | Has Video? |
|-------------|-------|---------------|--------------|--------------|-------------------|----------------------------|------------|
| `woodville-isd` | Woodville ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `west-hardin-ccisd` | West Hardin CCISD | Yes | draft | Local (Non-Placeholder) | Yes | Yes | No |
| `weslaco-isd` | Weslaco ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `waxahachie-isd` | Waxahachie ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `vernon-isd` | Vernon ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `valley-view-isd` | Valley View ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `trinity-isd` | Trinity ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `trenton-isd` | Trenton ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `tom-bean-isd` | Tom Bean ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `texas-facilities-commission` | Texas Facilities Commission | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `somerset-isd` | Somerset ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `skidmore-tynan-isd` | Skidmore-Tynan ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `silsbee-isd` | Silsbee ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `santa-fe-isd` | Santa Fe ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `sanger-isd` | Sanger ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `san-jacinto-community-college` | San Jacinto Community College | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `san-benito-cisd` | San Benito CISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `san-angelo-isd` | San Angelo ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `saint-jo-isd` | Saint Jo ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `rusk-isd` | Rusk ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `royal-isd` | Royal ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `roscoe-collegiate-isd` | Roscoe Collegiate ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `robstown-isd` | Robstown ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `ricardo-isd` | Ricardo ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `raymondville-isd` | Raymondville ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `prosper-isd` | Prosper ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `port-neches-groves-isd` | Port Neches-Groves ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `poolville-isd` | Poolville ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `pilot-point-isd` | Pilot Point ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `pflugerville-isd` | Pflugerville ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `pecos-isd` | Pecos ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `odem-edroy-isd` | Odem-Edroy ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `north-texas-medical-center` | North Texas Medical Center | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `normangee-isd` | Normangee ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `nocona-isd` | Nocona ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `new-boston-isd` | New Boston ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `needville-isd` | Needville ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `nacogdoches-isd` | Nacogdoches ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `moulton-isd` | Moulton ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `mesquite-isd` | Mesquite ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `mercedes-isd` | Mercedes ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `marble-falls-isd` | Marble Falls ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `manor-isd` | Manor ISD | Yes | publish | Local (Non-Placeholder) | Yes | Yes | No |
| `lyford-isd` | Lyford ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `lufkin-isd` | Lufkin ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `lubbock-isd` | Lubbock ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `llano-isd` | Llano ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `liberty-isd` | Liberty ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `lancaster-isd` | Lancaster ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `lamesa-isd` | Lamesa ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `lake-worth-isd` | Lake Worth ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `kountze-isd` | Kountze ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `kennedale-isd` | Kennedale ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `katy-isd` | Katy ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `jasper-isd` | Jasper ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `italy-isd` | Italy ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `ingram-isd` | Ingram ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `idea-public-schools` | IDEA Public Schools | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `houston-community-college` | Houston Community College | Yes | publish | Placeholder (Taj Mahal) | Yes | Yes | No |
| `hondo-isd` | Hondo ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `highland-park-isd` | Highland Park ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `hawkins-isd` | Hawkins ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `hardin-jefferson-isd` | Hardin-Jefferson ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `hardin-county` | Hardin County | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `gruver-isd` | Gruver ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `greenville-isd` | Greenville ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `goodall-witcher-hospital` | Goodall Witcher Hospital | Yes | publish | Placeholder (Taj Mahal) | Yes | Yes | No |
| `glen-rose-medical-center` | Glen Rose Medical Center | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `galena-park-isd` | Galena Park ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `gainesville-isd` | Gainesville ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `ferris-isd` | Ferris ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `ennis-isd` | Ennis ISD | Yes | draft | Local (Non-Placeholder) | Yes | Yes | No |
| `edgewood-isd` | Edgewood ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `edcouch-elsa-isd` | Edcouch-Elsa ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | No |
| `eagle-pass-isd` | Eagle Pass ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `e3-general` | E3_General | Yes | N/A | Unknown | No | N/A | No |
| `desoto-isd` | DeSoto ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `corsicana-isd` | Corsicana ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `cooke-county` | Cooke County | Yes | publish | Placeholder (Taj Mahal) | Yes | Yes | No |
| `columbia-brazoria-isd` | Columbia-Brazoria ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `cleveland-isd` | Cleveland ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `chico-isd` | Chico ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `cedar-hill-isd` | Cedar Hill ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `castleberry-isd` | Castleberry ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `caddo-mills-isd` | Caddo Mills ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `brownsville-isd` | Brownsville ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `brenham-isd` | Brenham ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `bowie-isd` | Bowie ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `big-sandy-isd` | Big Sandy ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `bellevue-isd` | Bellevue ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `banquete-isd` | Banquete ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `ballinger-isd` | Ballinger ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `baird-isd` | Baird ISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `anderson-shiro-cisd` | Anderson-Shiro CISD | Yes | draft | Placeholder (Taj Mahal) | Yes | Yes | No |
| `south-texas` | South Texas & Coast | Yes | publish | Local (Non-Placeholder) | No | N/A | No |
| `granbury-isd` | Granbury ISD | Yes | publish | Mapped (Real Image) | Yes | Yes | Yes |
| `bishop-cisd` | Bishop CISD | Yes | draft | Local (Non-Placeholder) | Yes | Yes | No |
| `donna-isd` | Donna ISD | Yes | publish | Local (Non-Placeholder) | No | N/A | No |
| `carrizo-springs-cisd` | Carrizo Springs CISD | Yes | publish | Local (Non-Placeholder) | No | N/A | No |
| `caldwell-isd` | Caldwell ISD | Yes | publish | Local (Non-Placeholder) | No | N/A | No |
| `little-elm-isd` | Little Elm ISD | No | draft | Placeholder (Taj Mahal) | Yes | Yes | Yes |
| `keene-isd` | Keene ISD | No | draft | Placeholder (Taj Mahal) | Yes | Yes | Yes |
| `plano-isd` | Plano ISD | No | draft | Placeholder (Taj Mahal) | Yes | Yes | Yes |
| `city-of-stockdale` | City of Stockdale | No | draft | Placeholder (Taj Mahal) | Yes | Yes | Yes |
| `gwh` | Goodall-Witcher Healthcare | No | publish | Mapped (Real Image) | No | N/A | No |
| `rio-hondo-isd` | Rio Hondo ISD | No | publish | Mapped (Real Image) | Yes | Yes | No |
| `boyd-isd` | Boyd ISD | No | publish | Mapped (Real Image) | Yes | Yes | Yes |
| `bryan-isd` | Bryan ISD | No | publish | Mapped (Real Image) | No | N/A | No |
