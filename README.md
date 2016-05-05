#Joomla! Collection, Archives and Repositories (JCar)

## Documentation

User documentation is available at https://www.gitbook.com/book/knowledgearcdotorg/jcar/.

## Build the project with Phing

You will need phing and composer installed to make these next steps work (installing these PHP programs and using them are not covered here).

Before you run phing you will need to copy the build.properties.example file to build.properties and then edit to match your development environment.

Running phing package will create installable packages under the build directory which is created within a local copy of the git code. The following packages are created:

**pkg_jcar.zip:** Contains all the core JCar extensions; the JCar component, the JCar content embed plugin and the JCar button plugin.

**plg_jcar_dspace.zip:** Integrates DSpace records using the KnowledgeArc REST API.

**plg_jcar_oai.zip:** Embeds OAI-compatible records.

Run phing help for all available targets.