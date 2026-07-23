const ids = [
  '1171901749', // Stockdale
  '946653874',  // Little Elm
  '1176712805', // Keene
  '1007829512', // Plano
  '1179578579', // Boyd
  '227283498',  // Granbury
  '740399213'   // Goodall-Witcher
];

async function run() {
  for (const id of ids) {
    try {
      const res = await fetch(`https://vimeo.com/api/v2/video/${id}.json`);
      if (res.ok) {
        const data = await res.json();
        console.log(`Video ID: ${id} -> Title: "${data[0].title}" | Embed Privacy: ${data[0].embed_privacy}`);
      } else {
        console.log(`Video ID: ${id} -> HTTP Error: ${res.status}`);
      }
    } catch (e) {
      console.log(`Video ID: ${id} -> Error: ${e.message}`);
    }
  }
}

run();
