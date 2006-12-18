package CampusMap;

import java.util.Vector;
import java.util.Hashtable;
import java.util.Enumeration;
import java.util.NoSuchElementException;
import processing.core.PConstants;
import nanoxml.*;

/** Class to manage all (visual) elements in the applet
 *  -Models
 *  -Gui elements
 *  -...
 */
public class ObjectManager{

	private StreamingManager streamingManager;

	//final XMLElement	XMLObjects	= new XMLElement("objects"); //"root" xml element
	private XMLElement	XMLObjects;
	//private FileReader	XMLReader;

	final int			NumberOfExecutionObjects = 1;

	//list of sub object managers
	public	Vector 		drawers;
	public	GuiManager	guiObjects;
	public	Vector		worldObjects;	//the vector containing all world objects
	public	Hashtable	buildingReferences;
	private boolean 	buildingsInvisible=false;

	//list of other objects
	CampusMap	applet;
	Camera	theCamera;

	Object	executionObjects[];

	public ObjectManager(CampusMap _applet, Camera _theCamera) {

		theCamera		= _theCamera;

		applet			= _applet;
		applet.env.objectInitDisplay.setText("ObjectManager");
		//sliderHolder 	= new GuiHolder(200, 200);
		if(applet instanceof CampusMap){
			guiObjects = new GuiManager();
		}
		worldObjects	= new Vector();

		drawers = new Vector();
		buildingReferences= new Hashtable();

		executionObjects	= new Object[NumberOfExecutionObjects];
		executionObjects[0]	= theCamera;

		try {


			//gui-file from url:
			//******************************************************************************
			XMLObjects	= new XMLElement();

			String[] guiXML = applet.loadStrings(Environment.address + Environment.guiFile);

			String wholeXMLString = new String();
			for (int i = 0; i < guiXML.length; i++)
				wholeXMLString += guiXML[i];
			XMLObjects.parseString(wholeXMLString);

			Enumeration childEnum = XMLObjects.enumerateChildren();

	        initGuiObjects((XMLElement) childEnum.nextElement());

	        // Init the SlideCases
	        for(int initDrawers = 0;initDrawers<drawers.size();initDrawers++)
	        	((SlideCase)drawers.get(initDrawers)).init();

			//geography-file from url:
			//******************************************************************************
			XMLObjects	= new XMLElement();

			String[] geoXML = applet.loadStrings(Environment.address + Environment.geoFile);

			String wholeXMLString2 = new String();
			for (int i = 0; i < geoXML.length; i++)
				wholeXMLString2 += geoXML[i];
			XMLObjects.parseString(wholeXMLString2);

			Enumeration childEnum2 = XMLObjects.enumerateChildren();

	        initWorldObjects((XMLElement) childEnum2.nextElement());

			//******************************************************************************
		} catch(XMLParseException xpe) {
			System.err.println("error opening and parsing file: " + xpe);
		} catch(Exception e){
			applet.env.setErrorDisplay("The Applet could not be initianted because the Configuration-file could not be loaded. Check the Internet-connection or access to local version Files.");
		}

		streamingManager = new StreamingManager(applet, worldObjects);
	}

	private void initGuiObjects(XMLElement guiGroups) {
		Enumeration childEnum = guiGroups.enumerateChildren();
		for(int j = 0; j < guiGroups.countChildren();j++){
			XMLElement guiObjects = (XMLElement)childEnum.nextElement();
			try {
				String slideCaseName = guiObjects.getStringAttribute("guiGroup");
				int side = guiObjects.getIntAttribute("side");
				int shift = guiObjects.getIntAttribute("shift");
				int occupy = guiObjects.getIntAttribute("occupy");
				int slideOut = guiObjects.getIntAttribute("slideOut");
				System.out.println("side "+side+" shift "+shift+" occupy: "+occupy+"slideOut: "+slideOut);

				// search all Drawers and get the one that has the name or create new and give it back
				SlideCase tempCase = null;
				if(drawers.size()!=0){
					for(int caseSearchIndex=0; caseSearchIndex<drawers.size();caseSearchIndex++)
						if(((SlideCase)drawers.get(caseSearchIndex)).getName().equals(slideCaseName)){
							tempCase = (SlideCase)drawers.get(caseSearchIndex);
							System.out.println("got same name: "+slideCaseName);
						}
				}
				// if none found create new one
				if(tempCase==null){
					tempCase=new SlideCase(applet, slideCaseName, side, shift, occupy, slideOut);
				}
				// get the Applet to give to the Element
				GuiObject[] tempObject = initSliders(guiObjects);
				tempCase.addContent(tempObject);
				System.out.println("inited tempObject "+tempObject);


				drawers.add(tempCase);
			} catch(XMLParseException xpe) {
				System.err.println("ObjectManager::initSlider() - attribute does not exist: " + xpe);
			}
		}
	}


	//init all Sliders stored in the xml file
	private GuiObject[] initSliders(XMLElement sliderObjects){
		GuiObject[] guiObjectReference = null;
		Enumeration childEnum = sliderObjects.enumerateChildren();

		XMLElement slider;
		XMLElement position = null;
		XMLElement size = null;
		XMLElement limitations = null;
		XMLElement startValue = null;
		XMLElement flags = null;
		XMLElement execution = null;
		XMLElement image = null;
		XMLElement helpText = null;



		try {
			int numChildren = sliderObjects.countChildren();
			guiObjectReference = new GuiObject[numChildren];
			for(int i = 0; i < numChildren;i++){
				slider		= (XMLElement) childEnum.nextElement();
				Enumeration sliderChildEnum = slider.enumerateChildren();
				String elemType = slider.getStringAttribute("type");
				boolean stateful = slider.getIntAttribute("keepState")==0?false:true;

				if(elemType.equals("Slider")){
					try{
						position	= (XMLElement) sliderChildEnum.nextElement();
						size		= (XMLElement) sliderChildEnum.nextElement();
						limitations	= (XMLElement) sliderChildEnum.nextElement();
						startValue	= (XMLElement) sliderChildEnum.nextElement();
						flags		= (XMLElement) sliderChildEnum.nextElement();
						execution	= (XMLElement) sliderChildEnum.nextElement();

						guiObjectReference[i] = new Slider(position.getIntAttribute("x"),position.getIntAttribute("y"),
								size.getIntAttribute("x"), size.getIntAttribute("y"),
								getFloatAttribute(limitations, "activeArea"),
								getFloatAttribute(startValue, "value"),
								(flags.getIntAttribute("isvertical")==0)?false:true
								);
						// add to Vector of ALL objects
						guiObjects.add(guiObjectReference[i]);
					}catch(NoSuchElementException e){
						System.err.println("");
					}
				}else if(elemType.equals("Overview")){
					position	= (XMLElement) sliderChildEnum.nextElement();
					size		= (XMLElement) sliderChildEnum.nextElement();

					guiObjectReference[i] = new Overview(
							position.getIntAttribute("x"),position.getIntAttribute("y"),
							size.getIntAttribute("x"), size.getIntAttribute("y")
							);
					// add to Vector of ALL objects
					guiObjects.add(guiObjectReference[i]);
				}else if(elemType.equals("Button")){
					try{
						position	= (XMLElement) sliderChildEnum.nextElement();
						size		= (XMLElement) sliderChildEnum.nextElement();
						execution	= (XMLElement) sliderChildEnum.nextElement();
						image		= (XMLElement) sliderChildEnum.nextElement();
						helpText	= (XMLElement) sliderChildEnum.nextElement();
					}catch(NoSuchElementException e){
						System.err.println("");
					}

					String img=null;
					int offset[] = new int[]{0,0};
					if(image != null){
						img = image.getStringAttribute("name");
						offset[0]=image.getIntAttribute("offsetX");
						offset[1]=image.getIntAttribute("offsetY");
					}
					String newLineString = helpText.getContent();
					int row = newLineString.indexOf("NEWLINE");
					if (row != -1)
						newLineString = newLineString.substring(0, row) +"\n"+ newLineString.substring(row + "NEWLINE".length(), newLineString.length());

					guiObjectReference[i] = new Button(
							position.getIntAttribute("x"),position.getIntAttribute("y"),
							size.getIntAttribute("x"), size.getIntAttribute("y"),
							(img!=null?img:null),
							offset,stateful,
							newLineString
							);
					// add to Vector of ALL objects
					guiObjects.add(guiObjectReference[i]);
				}else System.out.println("unknown 'type' of 'guiObject' in guiFile! Please refer to the documentation.");

				// Execution
				if(execution != null){
					boolean foundExecution = false;
					String executionString = execution.getStringAttribute("executionobject").toLowerCase();
					if(guiObjectReference[i]!=null && executionString!=null){
						System.out.println("executionString: "+executionString);
						for (int n = 0; n < executionObjects.length; n++) {
							if (executionObjects[n].getClass().getName().toLowerCase().indexOf(executionString)!=-1 ){
								guiObjectReference[i].registerExecution(
										execution.getStringAttribute("executionmethod"),
										executionObjects[n],
										(CampusMap) applet);
								foundExecution = true;
								break;
							}
						}
					}
					if (!foundExecution)
						System.err.println("ObjectManager::initSlider() - no execution object found");
				}

			}
		} catch(XMLParseException xpe) {
			System.err.println("ObjectManager::initSlider() - attribute does not exist: " + xpe);
		}
		return guiObjectReference;
	  }

	private void initWorldObjects(XMLElement worldObj) {
		try{
			Enumeration childEnum = worldObj.enumerateChildren();
			initBuildings((XMLElement) childEnum.nextElement());
			initEnvironment((XMLElement) childEnum.nextElement());
		} catch(Exception xpe) {
			System.err.println("initWorld Error: ");
			xpe.printStackTrace();
		}
	}

	private void initEnvironment(XMLElement envObjects) {

		Enumeration childEnum = envObjects.enumerateChildren();

		XMLElement envobject;
		XMLElement modelnames;
		XMLElement flags;

		try {
			for(int i = 0; i < envObjects.countChildren();i++){
				envobject	= (XMLElement) childEnum.nextElement();
				Enumeration envChildEnum = envobject.enumerateChildren();
				FVector envPos = getFVectorFromChild((XMLElement)envChildEnum.nextElement());
				FVector envSca = getFVectorFromChild((XMLElement)envChildEnum.nextElement());
				FVector envRot = getFVectorFromChild((XMLElement)envChildEnum.nextElement());
				modelnames	= (XMLElement)envChildEnum.nextElement();
				flags		= (XMLElement)envChildEnum.nextElement();

				int numberOfNames = countEnumChildren(modelnames.enumerateAttributeNames());
				String[] names = new String[numberOfNames];
				for (int n = 0; n < numberOfNames; n++){
					names[n] = modelnames.getStringAttribute("lod"+ n);
				}

				worldObjects.add( new ObjectOfInterest(
						applet,	envPos, envSca, envRot, names, Environment.address+Environment.modelFolder, false,
						((flags.getIntAttribute("drawLines")==0)?false:true),
						((flags.getIntAttribute("zFade")==0)?false:true))
						);
				((ObjectOfInterest)worldObjects.lastElement()).hideOnTour = ((flags.getIntAttribute("hideOnTour")==0)?false:true);

			}
		} catch(XMLParseException xpe) {
				System.err.println("ObjectManager::initEnvironment() - attribute does not exist: " + xpe);
		}
	}


	private void initBuildings(XMLElement buildings) {

		Enumeration childEnum = buildings.enumerateChildren();
		float  uniScale = (float)buildings.getDoubleAttribute("uniformScale");
		float  databaseScale = (float)buildings.getDoubleAttribute("datebaseScale");
		if(applet instanceof CampusMap) {
			((CampusMap)applet).setBuildingUniformScale(uniScale);
			((CampusMap)applet).setBuildingDatabaseScale(databaseScale);
		}

		XMLElement buildingobject;
		XMLElement modelnames;
		XMLElement flags;
		XMLElement colspheres;
		XMLElement colrects;
		Enumeration information;
		Enumeration detailview;

		int numBuildings = buildings.countChildren();
		//buildingReferences = new Building[numBuildings];
		int buildingIndex=0;
		try {
			for(buildingIndex = 0; buildingIndex < numBuildings;buildingIndex++){

				buildingobject	= (XMLElement) childEnum.nextElement();
				Enumeration builChildEnum = buildingobject.enumerateChildren();
				FVector builPos = getFVectorFromChild((XMLElement)builChildEnum.nextElement());
				FVector builSca = getFVectorFromChild((XMLElement)builChildEnum.nextElement());
				FVector builRot = getFVectorFromChild((XMLElement)builChildEnum.nextElement());
				modelnames	= (XMLElement)builChildEnum.nextElement();
				flags		= (XMLElement)builChildEnum.nextElement();
				colspheres	= (XMLElement)builChildEnum.nextElement();
				colrects	= (XMLElement)builChildEnum.nextElement();
				information	= ((XMLElement)builChildEnum.nextElement()).enumerateChildren();
				detailview	= ((XMLElement)builChildEnum.nextElement()).enumerateChildren();

				// flip buildPos Dimension
				builPos.setY(-builPos.getY());

				int numberOfNames = modelnames.getIntAttribute("lodNum");
				String[] names = new String[numberOfNames];
				for (int n = 0; n < numberOfNames; n++){
					//System.out.println("Modellname zum laden: "+modelnames.getStringAttribute("name")+"_"+ObjectOfInterest.lods[n]);
					names[n] = modelnames.getStringAttribute("name")+"_"+ObjectOfInterest.lods[n]+".gz";
				}

				worldObjects.add( new Building(applet, builPos, builSca, builRot, names, Environment.address+Environment.modelFolder,
						((flags.getIntAttribute("drawLines")==0)?false:true),
						((flags.getIntAttribute("zFade")==0)?false:true))
						);

				//Collision Spheres
				int numberOfColSpheres = colspheres.countChildren();
				Enumeration sphereEnum = colspheres.enumerateChildren();
				((Building)worldObjects.lastElement()).collisionSpheres = new CollisionSphere[numberOfColSpheres];
				for (int n = 0; n < numberOfColSpheres; n++)
				{
					XMLElement sphere = (XMLElement) sphereEnum.nextElement();
					FVector spherePos = getFVectorFromChild(sphere);
					spherePos.setZ(spherePos.getZ() * -1);
					((Building)worldObjects.lastElement()).collisionSpheres[n]
					     = new CollisionSphere(spherePos, getFloatAttribute(sphere, "radius")/1.5f);
					//((Building)worldObjects.lastElement()).collisionSpheres[n].rotateModelSpaceXYZ(builRot);
					((Building)worldObjects.lastElement()).collisionSpheres[n].rotateModelSpaceXYZ(new FVector(PConstants.PI, 0, 0));
					((Building)worldObjects.lastElement()).collisionSpheres[n].scaleAbout(FVector.multiply(builSca, uniScale));
					((Building)worldObjects.lastElement()).collisionSpheres[n].moveAbout(FVector.multiply(builPos, uniScale));
				}

//				Collision Rectangles
				int numberOfColRects = colrects.countChildren();
				((Building)worldObjects.lastElement()).collisionRectangles = new CollisionRectangle[numberOfColRects];
				if(numberOfColRects > 0){
					Enumeration rectEnum = colrects.enumerateChildren();
					for (int n = 0; n < numberOfColRects; n++)
					{
						XMLElement rect = (XMLElement) rectEnum.nextElement();
						FVector p1 = getFVectorFromChild(rect);
						FVector p2 = new FVector(getFloatAttribute(rect, "a"), getFloatAttribute(rect, "b"), getFloatAttribute(rect, "c"));
						p1.printMe();
						p2.printMe();
						((Building)worldObjects.lastElement()).collisionRectangles[n]
						     = new CollisionRectangle(p1, p2, rect.getIntAttribute("AlignedToAxis"));
						//((Building)worldObjects.lastElement()).collisionRectangles[n].rotateToNewAxisAlignment(2);
						((Building)worldObjects.lastElement()).collisionRectangles[n].scaleAbout(FVector.multiply(builSca, uniScale));
						((Building)worldObjects.lastElement()).collisionRectangles[n].moveAbout(FVector.multiply(builPos, uniScale));
					}
				}

				//Informations
				String tempBuildNo = ((XMLElement)information.nextElement()).getStringAttribute("buildingnumber");
				((Building)worldObjects.lastElement()).myBuildingNo = tempBuildNo;
				try{
					buildingReferences.put(tempBuildNo, (Building)worldObjects.lastElement());
				}catch(ArrayIndexOutOfBoundsException e){
					System.err.println("Da is schon n Gebäude");
				}
				XMLElement text = ((XMLElement)information.nextElement());
				String headLine = text.getStringAttribute("headline");
				((Building)worldObjects.lastElement()).shortDescription = headLine;
				String infoText = text.getStringAttribute("infotext");
				((Building)worldObjects.lastElement()).longDescription = infoText;

				XMLElement roomCoordOrigin = (XMLElement) detailview.nextElement();
				((Building)worldObjects.lastElement()).roomCoordOrigin = getFVectorFromChild(roomCoordOrigin);
				((Building)worldObjects.lastElement()).roomCoordRotation = this.getFloatAttribute(roomCoordOrigin, "zRotate");
				//DetailView
				((Building)worldObjects.lastElement()).entrancePosition		= getFVectorFromChild((XMLElement) detailview.nextElement());
				XMLElement rotationSettings = (XMLElement) detailview.nextElement();
				((Building)worldObjects.lastElement()).flyAroundRadius		= getFloatAttribute(rotationSettings, "flyAroundRadius");
				((Building)worldObjects.lastElement()).flyAroundCenterHeight= getFloatAttribute(rotationSettings, "flyAroundCenterHeight");




			}
		} catch(Exception xpe) {
				System.err.println("ObjectManager::initBuildings() - attribute does not exist for building "+buildingIndex+": ");
				xpe.printStackTrace();
		}

	}

	private FVector getFVectorFromChild(XMLElement vectorChild) {
		FVector returnVector = new FVector();
		//Enumeration attriEnum = ((XMLElement)vectorChild).get.enumerateAttributeNames();
		returnVector.set(
				getFloatAttribute(vectorChild, "x"),
				getFloatAttribute(vectorChild, "y"),
				getFloatAttribute(vectorChild, "z"));
		return returnVector;
	}

	private float getFloatAttribute(XMLElement child, String attriName) {
		return Float.valueOf(child.getStringAttribute(attriName)).floatValue();
	}

	private int countEnumChildren(Enumeration childEnum) {
		int numChildren = 0;
		while (childEnum.hasMoreElements()) {
			numChildren++;
			childEnum.nextElement();
		}
		return numChildren;
	}

	public CollisionSphere testBuildingsWithPoint(FVector point, float avoidingDistance) {
		for(int n = 0;n < worldObjects.size(); n++) {
			if (((ObjectOfInterest)(worldObjects.elementAt(n))).selectable) {
				CollisionSphere sphere = ((Building)(worldObjects.elementAt(n))).testColSpheresWithPoint(point, avoidingDistance);
				if (sphere != null)	return sphere;
			}
		}
		return null;
	}

	public Building getBuildingByNumber(String number) {
		Object returnValue=null;
		if(buildingReferences.containsKey(number)) returnValue=buildingReferences.get(number);
		else System.err.println("Still no building loaded for position "+number);
		return (Building)returnValue;
	}

	public void makeBuildingsInvisible(Building exception) {
		if(!buildingsInvisible){
//		Enumeration keys = buildingReferences.keys();
//		for (int i = 0; i < buildingReferences.size(); i++) {
//			Object actKey = keys.nextElement();
//			if(buildingReferences.containsKey(actKey))
//				if (actKey.equals(exception))
//					((Building)buildingReferences.get(actKey)).drawingActive = true;
//				else
//					((Building)buildingReferences.get(actKey)).drawingActive = false;
//		}
			for(int i = 0;i < worldObjects.size(); i++) {
				if (( (((ObjectOfInterest)(worldObjects.elementAt(i))).hideOnTour ) ||
					( !worldObjects.elementAt(i).equals(exception) )) ){
					((ObjectOfInterest)(worldObjects.elementAt(i))).drawingActive = false;
				}
			}
			buildingsInvisible=true;
			System.out.println("Buildings are invisible");
		}else System.out.println("Buildings were already invisible");
	}

	public void resetBuildings() {
		Enumeration keys = buildingReferences.keys();
		for (int i = 0; i < buildingReferences.size(); i++) {
			Object actKey = keys.nextElement();
			if(buildingReferences.containsKey(actKey)){
				((Building)buildingReferences.get(actKey)).drawingActive = true;
				((Building)buildingReferences.get(actKey)).clearPositionInBuilding();
				((Building)buildingReferences.get(actKey)).setForceSelectedModel(false);
			}
		}
		for(int i = 0;i < worldObjects.size(); i++) {
			if (!(((ObjectOfInterest)(worldObjects.elementAt(i))).selectable)) {
				((ObjectOfInterest)(worldObjects.elementAt(i))).drawingActive = true;
			}
		}
		System.out.println("Building alpha reset");
		buildingsInvisible=false;
	}


} //end of class ObjectManager
