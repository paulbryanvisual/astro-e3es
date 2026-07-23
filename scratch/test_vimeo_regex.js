function parseVimeo(val) {
    var cleanVal = val.trim();
    if (cleanVal) {
        // Find the video ID (at least 8 digits)
        var idMatch = cleanVal.match(/(?:channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|manage\/videos\/|showcase\/[^\/]+\/video\/|video\/|vimeo\.com\/|^)([0-9]{8,})/i);
        if (idMatch && idMatch[1]) {
            var videoId = idMatch[1];
            var hash = '';
            
            // Check if there is an alphanumeric hash immediately following the video ID
            var remaining = cleanVal.substring(idMatch.index + idMatch[0].length);
            var postMatch = remaining.match(/^\/([a-zA-Z0-9]+)/);
            if (postMatch) {
                // Make sure the hash is not just another URL path or parameter
                var possibleHash = postMatch[1];
                if (possibleHash.toLowerCase() !== 'dnt' && possibleHash.toLowerCase() !== 'badge') {
                    hash = possibleHash;
                }
            } else {
                // Check if there is an h= query parameter in the URL
                var hMatch = cleanVal.match(/[?&]h=([a-zA-Z0-9]+)/);
                if (hMatch) {
                    hash = hMatch[1];
                }
            }
            
            // Reconstruct the embed URL
            cleanVal = 'https://player.vimeo.com/video/' + videoId;
            var params = [];
            if (hash) {
                params.push('h=' + hash);
            }
            params.push('badge=0', 'autopause=0', 'player_id=0', 'app_id=58479');
            cleanVal += '?' + params.join('&');
        }
    }
    return cleanVal;
}

const testUrls = [
    '935503628',
    'https://vimeo.com/935503628',
    'https://player.vimeo.com/video/935503628',
    'https://vimeo.com/manage/videos/935503628',
    'https://vimeo.com/showcase/935503628/video/935503628',
    'https://vimeo.com/channels/staffpicks/935503628',
    'https://vimeo.com/groups/animation/videos/935503628',
    'https://vimeo.com/935503628/d12c83b8f2',
    'https://player.vimeo.com/video/935503628?h=d12c83b8f2',
    'https://vimeo.com/manage/videos/935503628/d12c83b8f2',
    'https://player.vimeo.com/video/935503628?h=d12c83b8f2&dnt=1'
];

for (const url of testUrls) {
    console.log(`Input: "${url}"\nOutput: "${parseVimeo(url)}"\n`);
}
