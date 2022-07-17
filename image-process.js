const axios = require("axios"); //* importing the axios package
const sharp = require("sharp"); //* importing the sharp package
const imageType = require('image-type');
const extensions = ["jpg", "jpeg", "png", "tif"];

const filename = process.argv[2]
const url = process.argv[3];
const directory = process.argv[4];
const width = Number(process.argv[5]);
const height = Number(process.argv[6])

axios.get(url, {responseType: 'arraybuffer'})
    .then((response) => {
        // converts the arraybuffer to base64
        const buffer = Buffer.from(response.data, "base64");

        const ext = imageType(buffer).ext.toLowerCase();

        // Check that the image type is supported
        if (!extensions.includes(ext) || ext === null) {
            throw "Unsupported image type.";
        }

        sharp(buffer)
            .resize(width, height, {fit: 'inside'}) // max 600k
            .jpeg({
                quality:92
            }).sharpen()
            .toFile(`${directory}/${filename}`)
            .then(() => {
                console.log(true)
            })
    })
    .catch((err) => {
        console.log(false);
        console.log(`Couldn't process image: ${err}`);
    })

/*
4:30
async function constraintImage(buffer, quality = 82, drop = 2) {

    const done = await sharp(buffer).resize({
        width: 1000,
        height: 1000,
        fit: sharp.fit.inside
    }).jpeg({
        quality
    }).toBuffer();

    if (done.byteLength > 2000000) {
        return constraintImage(buffer, quality - drop);
    }

    return done;
}
 */