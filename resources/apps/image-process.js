const axios = require("axios"); //* importing the axios package
const sharp = require("sharp"); //* importing the sharp package

const filename = process.argv[2]
const url = process.argv[3];

axios.get(url, {responseType: 'arraybuffer'})
    .then((response) => {
        // converts the arraybuffer to base64
        const buffer = Buffer.from(response.data, "base64");

        sharp(buffer)
            .resize({
                width: 300,
                height: 300,
                fit: 'inside'
            }) // max 1000k
            .jpeg({
                quality:93,
                mozjpeg: true
            })
            .toFile(`CURRENT_PATH/storage/app/public/thumbs/300_300/${filename}`)
            .then(() => {
                console.log(true)
            })
    })
    .catch((err) => {
        console.log(false);
        console.log(`Couldn't process image: ${err}`);
    })