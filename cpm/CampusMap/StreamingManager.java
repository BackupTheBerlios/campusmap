package CampusMap;

import java.util.*;
import processing.core.PApplet;

/** Class to manage all streaming elements that get loaded into the applet while it is running.
 *  -Models
 *  -...
 */
public class StreamingManager extends Thread {
	
	static	final	int	SIMULTAN_FILES		= 2;
	static	final	int	MAX_LOD_LEVEL		= 3;
	PApplet applet;
	Vector worldObjects;
	StreamingFile streamingFiles[];
	int numLoadedFiles = 0;
	int numFilesToLoad = 0;
	int numObjectsToLoad = 0;
	int initedObjCounter = 0;
	int lodToLoad = 0;
	boolean load2Loaded = false;
	final int initMinLod = 0;
	boolean initMinLodReached;
	
	public StreamingManager(PApplet p_applet, Vector p_worldObjects) {
		applet = p_applet;
		initMinLodReached=false;
		worldObjects = p_worldObjects;
		numObjectsToLoad = worldObjects.size();
		numFilesToLoad = findNumberOfFiles();
		streamingFiles = new StreamingFile[SIMULTAN_FILES];
		/*for (int i = 0; i<SIMULTAN_FILES; i++)
			if(p_applet instanceof CampusMap)
				streamingFiles[i] = new StreamingFile((CampusMap)p_applet);
			else streamingFiles[i] = new StreamingFile();*/
		start();
	}
	
	private int findNumberOfFiles() {
		int number = 0;
		for (int i = 0; i < numObjectsToLoad; i++) {
			number += ((ObjectOfInterest)worldObjects.elementAt(i)).getNumberOfLodModels();
		}
		return number;
	}
	
	private void findNewFileToLoad(int slot) {
		int fileNo = 0;
		boolean foundModel = false;
		while (!foundModel && fileNo < numFilesToLoad && lodToLoad < MAX_LOD_LEVEL) {
			int i = 0;
			for (/*void*/; i < numObjectsToLoad; i++) {
				//System.out.println("is Thread for model " + i + " with lod " + lodToLoad+" already done?");
				if ((!((ObjectOfInterest)(worldObjects.elementAt(i))).getLodModelBeingLoaded(lodToLoad)) && (!((ObjectOfInterest)(worldObjects.elementAt(i))).getLodModelLoaded(lodToLoad))) {
					numLoadedFiles++;
					foundModel = true;
					//System.out.println("using slot " + slot + " to load model " + i + " with lod " + lodToLoad + ".  So far " + numLoadedFiles + " of " + numFilesToLoad + " files loaded.");
					((ObjectOfInterest)(worldObjects.elementAt(i))).setLodModelBeingLoaded(lodToLoad, true);
					if(applet instanceof CampusMap)
						streamingFiles[slot] = new StreamingFile((CampusMap)applet, this);
					else streamingFiles[slot] = new StreamingFile();
					streamingFiles[slot].setPriority(MIN_PRIORITY);
					if (i == numObjectsToLoad || isLastModelInThisLod(i)) {
						streamingFiles[slot].registerNotify();
					}
					streamingFiles[slot].startModelLoading(((ObjectOfInterest)worldObjects.elementAt(i)), lodToLoad);
					break;
				}
			}
			if (i == numObjectsToLoad || isLastModelInThisLod(i)) {
				System.out.println("Level "+lodToLoad+" loaded");
				streamingFiles[slot].registerNotify();
				lodToLoad++;
			}
		}
	}
	
	public void notifyInitedObj(){
		((CampusMap)applet).startDrawing();//invokeDisplay call should be here
	}
	
	private boolean isLastModelInThisLod(int modelNo) {
		modelNo++;
		for (/*void*/; modelNo < numObjectsToLoad; modelNo++) {
			if (((ObjectOfInterest)worldObjects.elementAt(modelNo)).getNumberOfLodModels()>=lodToLoad) {
				return false;
			}
		}
		return true;
	}
	
	public void run() {
		//System.out.println("load lods loop started: " + numLoadedFiles + " of " + numFilesToLoad + " files loaded.");
		while (numLoadedFiles != numFilesToLoad && lodToLoad < MAX_LOD_LEVEL) {
			for (int i = 0; i < SIMULTAN_FILES; i++) {
				if (streamingFiles[i] == null || streamingFiles[i].isDone()) {
					//System.out.println("slot " + i + " is empty and can now be used.");
					findNewFileToLoad(i);
				}
			}
			try {
				Thread.sleep(150);
			} catch(InterruptedException ie) {
				System.err.println("Insomnia @ StreamingManager");
			}
		}
		//Environment.setToolTip("Geometrie komplett geladen.", 0); 
		// Commented because overrides error message if internet connection doesn't work
	}

}

class StreamingFile extends Thread {
	
	boolean done = true;
	boolean notify= false;
	StreamingManager streamManager;
	int lodToLoad = 0;
	ObjectOfInterest modelToLoad;
	int runNumber = 0;
	CampusMap applet;
	
	public StreamingFile(){
		/**/
	}

	public StreamingFile(CampusMap p_applet, StreamingManager p_streamManager){
		streamManager = p_streamManager; 
		applet = p_applet;
		setPriority(Thread.MIN_PRIORITY);
	}
	
	public void startModelLoading(ObjectOfInterest p_modelToLoad, int p_lodToLoad) {
		done = false;
		runNumber=0;
		modelToLoad = p_modelToLoad;
		lodToLoad = p_lodToLoad;
		if(applet!=null)//applet.env.objectInitDisplay.setText(modelToLoad.modelsToLoad[lodToLoad]);
		try{
			start();
		}catch(IllegalThreadStateException e){
			System.err.println("Problem with Thread of file: "+modelToLoad.modelsToLoad[lodToLoad]);
			//System.out.println("Has state: "+getState());
			e.printStackTrace();
		}
	}
	
	public void registerNotify(){
		notify=true;
	}
	
	public void run() {
		modelToLoad.loadModel(lodToLoad);
		//System.out.println("Thread is running the "+(++runNumber)+"st time with file:"+modelToLoad.modelsToLoad[lodToLoad]);
		done = true;
		if(notify)streamManager.notifyInitedObj();
	}
	
	public boolean isDone() {
		return done;
	}
}