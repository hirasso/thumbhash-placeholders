import fs from "fs";
import path from "path";
import { parseArgs } from "util";
import { dd } from "./utils.js";

// Read the version and name from package.json
const packageJsonPath = path.join(process.cwd(), "package.json");
const { version } = JSON.parse(fs.readFileSync(packageJsonPath, "utf8"));

const { positionals: files } = parseArgs({
  allowPositionals: true,
});

if (!files.length) {
  throw new Error("please provide at least one file name");
}

for (const fileName of files) {
  patchVersion(fileName, version);
}

/**
 * Patch the version in a file
 * @param {string} fileName
 * @param {version} version
 */
function patchVersion(fileName, version) {
  const filePath = path.join(process.cwd(), fileName);

  let file = fs.readFileSync(filePath, "utf8");

  // Update version in a PHP file
  if (fileName.endsWith(".php")) {
    file = file.replace(/Version:\s*\d+\.\d+\.\d+/, `Version: ${version}`);
  }

  // Update version in a readme.txt file
  if (fileName === "readme.txt") {
    file = file.replace(
      /Stable Tag:\s*\d+\.\d+\.\d+/,
      `Stable Tag: ${version}`,
    );
  }

  // Update version in a composer.json file
  if (fileName === "composer.json") {
    file = file.replace(
      /"version":\s+"\d+\.\d+\.\d+",/,
      `"version": "${version}",`,
    );
  }

  // Write the file
  fs.writeFileSync(filePath, file, "utf8");

  console.log(`✅ Updated version to ${version} in ${fileName}`);
}
