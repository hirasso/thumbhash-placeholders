#!/bin/bash

# Check if .gitattributes exists
if [[ ! -f .gitattributes ]]; then
  echo ".gitattributes file not found!"
  exit 1
fi

# Check if .gitignore exists
if [[ ! -f .gitignore ]]; then
  echo ".gitignore file not found!"
  exit 1
fi

# Create or overwrite the .distignore file
> .distignore

echo -e "\n#----------\n# Automatically generated from .gitignore: \n#----------\n" >> .distignore

# Read .gitignore line by line, including last line without a newline
while IFS= read -r line || [[ -n "$line" ]]; do
  # Aadd to .distignore
  echo "${line}" >> .distignore
done < .gitignore

echo -e "\n#----------\n# Automatically generated from .gitattributes: \n#----------\n" >> .distignore

# Read .gitattributes line by line, including last line without a newline
while IFS= read -r line || [[ -n "$line" ]]; do
  # Check if the line ends with export-ignore
  if [[ "$line" == *" export-ignore" ]]; then
    # Remove export-ignore directive and add to .distignore
    echo "${line% export-ignore}" >> .distignore
  fi
done < .gitattributes

echo "✔ .distignore file created from .gitattributes and .gitignore."

# Remove lines starting with "vendor" from .distignore
sed -i.bak '/^vendor/d' .distignore
rm -f .distignore.bak

echo "✔︎ Lines starting with 'vendor' have been removed from .distignore."