package CampusMap;

import java.util.*;
import processing.core.PApplet;
//import javax.swing.*;

/** Class to manage all streaming elements that get loaded into the applet while it is running.
 *  -Models
 *  -...
 */
public class StreamingManager extends Thread {

	static	final	int	SIMULTAN_FILES		= 3;
	static	final	int	MAX_LOD_LEVEL		= 2;
	PApplet applet;
	Vector worldObjects;
	Vector currLodObjectsStillToLoad;
	StreamingFile streamingFiles[];
	int numLoadedFiles = 0;
	int numFilesToLoad = 0;
	int numObjectsToLoad = 0;
	int initedObjCounter = 0;
	int lodToLoad = 0;
	boolean load2Loaded = false;
	final int initMinLod = 0;
	boolean initMinLodReached;
	//JFrame ladeFenster;

	public StreamingManager(PApplet p_applet, Vector p_worldObjects) {
		applet = p_applet;
		((CampusMap)applet).env.objectInitDisplay.setText("StreamingManager");
		initMinLodReached=false;
		worldObjects = p_worldObjects;
		currLodObjectsStillToLoad = new Vector();
		numObjectsToLoad = worldObjects.size();
		numFilesToLoad = findNumberOfFilesForWorldObj();
		streamingFiles = new StreamingFile[SIMULTAN_FILES];
		/*for (int i = 0; i<SIMULTAN_FILES; i++)
                  streamingFiles[i] = new StreamingFile(p_applet, this);
                */
		start();
	}

	private int findNumberOfFilesForWorldObj() {
		int number = 0;
		for (int i = 0; i < numObjectsToLoad; i++) {
			number += ((ObjectOfInterest)worldObjects.elementAt(i)).getNumberOfLodModels();
		}
		return number;
	}

	private void findNewFileToLoad(int slot) {
		int fileNo = 0;
		boolean foundModel = false;

		Vector leftToLoad = leftToLoadInThisLod();
		if (leftToLoad.size()>0) {
				((CampusMap)applet).env.objectInitDisplay.setText(((ObjectOfInterest)(leftToLoad.elementAt(0))).modelsToLoad[lodToLoad]);
				((ObjectOfInterest)(leftToLoad.elementAt(0))).setLodModelBeingLoaded(lodToLoad, true);
				streamingFiles[slot] = new StreamingFile((CampusMap)applet, this);
				streamingFiles[slot].setPriority(MIN_PRIORITY);

				if (leftToLoad.size()==1) {
					if(lodToLoad == initMinLod)streamingFiles[slot].registerNotify();
				}
				streamingFiles[slot].startModelLoading(((ObjectOfInterest)leftToLoad.elementAt(0)), lodToLoad);
		}else if (leftToLoad.size()==0){
			System.out.println("Level "+lodToLoad+" loaded");
			lodToLoad++;
		}
	}

	private Vector leftToLoadInThisLod() {
		Vector rueckgabe=new Vector();
		for (int testIndex=0; testIndex < numObjectsToLoad; testIndex++) {
			// if all others return "loaded"
			if (((ObjectOfInterest)(worldObjects.elementAt(testIndex))).getNumberOfLodModels()>lodToLoad &&
				(!((ObjectOfInterest)(worldObjects.elementAt(testIndex))).getLodModelBeingLoaded(lodToLoad)) &&
				(!((ObjectOfInterest)(worldObjects.elementAt(testIndex))).getLodModelLoaded(lodToLoad)) ){
				rueckgabe.add(worldObjects.elementAt(testIndex));
			}else{
				numLoadedFiles++;
			}
		}
//		System.out.println("arraygroesse"+rueckgabe.size());
		return rueckgabe;
	}

	public void run() {
		((CampusMap)applet).env.initDisplay.setText("loading geometry files");
		while (lodToLoad < MAX_LOD_LEVEL && numLoadedFiles != numFilesToLoad) {
//			System.out.println("load lods loop started: " + numLoadedFiles + " of " + numFilesToLoad + " files loaded.");
			for (int i = 0; i < SIMULTAN_FILES; i++) {
				if (streamingFiles[i] == null || streamingFiles[i].isDone()) {
					//System.out.println("slot " + i + " is empty and can now be used.");
					findNewFileToLoad(i);
				}
			}
			try {
				Thread.sleep(300);
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
	PApplet applet;

	public StreamingFile(){
		/**/
	}

	public StreamingFile(PApplet p_applet, StreamingManager p_streamManager){
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
		if(notify)modelToLoad.notifyInitedObj();
		modelToLoad.loadModel(lodToLoad);
		//System.out.println("Thread is running the "+(++runNumber)+"st time with file:"+modelToLoad.modelsToLoad[lodToLoad]);
		done = true;
	}

	public boolean isDone() {
		return done;
	}
}
