const axios = require("axios"); //* importing the axios package
const sharp = require("sharp"); //* importing the sharp package
const imageType = require('image-type');
const extensions = ["jpg", "jpeg", "png", "tif"];

const filename = process.argv[2]
const url = process.argv[3];
const directory = process.argv[4];
const w = Number(process.argv[5]);
const h = Number(process.argv[6])

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
            .resize({
                width: w,
                height: h,
                fit: 'inside'
            }) // max 1000k
            .jpeg({
                quality:93,
                mozjpeg: true
            })
            .toFile(`${directory}/${filename}`)
            .then(() => {
                console.log(true)
            })
    })
    .catch((err) => {
        console.log(false);
        console.log(`Couldn't process image: ${err}`);
    })
