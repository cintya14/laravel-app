#!/bin/bash
# post-build.sh

echo "Moviendo assets construidos a directorio seguro..."
mv public/build /tmp/railway-build-assets

echo "Assets movidos a /tmp/railway-build-assets"
ls -la /tmp/railway-build-assets/