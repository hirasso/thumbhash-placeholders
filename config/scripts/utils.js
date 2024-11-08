/**
 * Dump and die
 * @param {Array<any>} args
 */
export function dd(...args) {
  console.log(...args);
  process.exit();
}
