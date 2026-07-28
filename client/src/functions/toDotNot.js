export default function toDotNot(input, parentKey) {
  return Object.keys(input || {}).reduce((acc, key) => {
    const value = input[key];
    const outputKey = parentKey ? `${parentKey}.${key}` : `${key}`;

    // NOTE: remove `&& (!Array.isArray(value) || value.length)` to exclude empty arrays from the output
    if (value && typeof value === 'object' && (!Array.isArray(value) || value.length)) return ({ ...acc, ...toDotNot(value, outputKey) });

    return ({ ...acc, [outputKey]: value });
  }, {});
}
