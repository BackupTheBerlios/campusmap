package CampusMap;

import objLoader.OBJModel;
import processing.core.PApplet;

/** Class describing main funcionality of the objects on the surface 
 *  which are selectable for further informations  
 * 
 */
public class ObjectOfInterest{
	
	// static and final Definitions

	protected int currentDetailLevel;
	protected int oldDetailLevel;
	protected boolean selectable;
	protected boolean drawLines;
	protected boolean zFade;
	protected FVector myPos;
	protected FVector myRot;
	protected FVector myScale;
	protected CampusMap myParentApplet;
	protected String[] modelsToLoad;
	protected OBJModel myModels[];
	protected boolean modelsLoaded[] = {false, false, false};
	protected boolean wholeObjectIsInited;
	protected boolean modelsBeingLoaded[];
	protected String modelName;
	protected String address;
	protected float myAlpha = 1.0f;
	protected boolean drawingActive = true;
	
	protected boolean hideOnTour = false;
	
	// detail-constants
	static final int OVERVIEW = 0, CLOSER = 1, SELECTED = 2;
	final static String[] lods = {"OVERVIEW", "CLOSER", "SELECTED"};
	
	// distance at which to force a detail-change 
	static final int CLOSER_DISTANCE = 400;
	static final int DETAIL_DISTANCE = 100;
	
	public ObjectOfInterest(){/**/}
	
	public ObjectOfInterest(CampusMap drawApplet, FVector p_myPos, FVector p_myScale, FVector p_myRot, String[] p_modelsToLoad, String p_address, boolean p_selectable, boolean p_drawLines, boolean p_zFade){
		myParentApplet = (CampusMap)drawApplet;
		myParentApplet.env.objectInitDisplay.setText("Objects of Interest");
		selectable = p_selectable;
		drawLines = p_drawLines;
		zFade = p_zFade; 
		
		//oldDetailLevel=-1;
		currentDetailLevel=OVERVIEW;
		this.myPos		= p_myPos;
		this.myScale	= p_myScale;
		this.myRot		= p_myRot;
		modelsToLoad	= p_modelsToLoad;
		address			= p_address;
		myModels		= new OBJModel[modelsToLoad.length];
		modelsBeingLoaded = new boolean[modelsToLoad.length];
		wholeObjectIsInited=false;
		for (int i = 0; i< modelsToLoad.length; i++)
		{
			modelsLoaded[i] = false;
			modelsBeingLoaded[i] = false;
		}
	}
	
	public int getNumberOfLodModels() {
		return myModels.length;
	}
	
	public boolean getLodModelLoaded(int lod) {
		if (lod > (getNumberOfLodModels()-1) || modelsLoaded[lod])
			return true;
		return false;
	}
	
	public boolean getLodModelBeingLoaded(int lod) {
		if (lod > (getNumberOfLodModels()-1) || modelsBeingLoaded[lod])
			return true;
		return false;
	}
	
	public void setLodModelBeingLoaded(int lod, boolean beingLoaded) {
		if (lod <= (getNumberOfLodModels()-1))
			modelsBeingLoaded[lod] = beingLoaded;
			
	}	
	
	// Model loading
	public void loadModel(int modelNo){
		myModels[modelNo] = new OBJModel(myParentApplet);
		myModels[modelNo].load(address, modelsToLoad[modelNo]);
		//myModels[modelNo].load(myParentApplet.getClass().getResourceAsStream("data/"+modelsToLoad[modelNo]));
		myModels[modelNo].drawMode(processing.core.PConstants.TRIANGLES);
		myModels[modelNo].setLineDrawing(drawLines);
		myModels[modelNo].setZFade(zFade);
		myModels[modelNo].setFadeMidPoint(new float[]{0.0f, 0.0f, 0.0f});
		modelsLoaded[modelNo] = true;
		modelsBeingLoaded[modelNo] = false;
	}

	// draw method
	public void draw(PApplet myDrawApplet){
		// drawing of the instance
		if (drawingActive && modelsLoaded[currentDetailLevel]) {
			myDrawApplet.pushMatrix();
			myDrawApplet.translate(myPos.getX(), myPos.getY(), myPos.getZ());
			myDrawApplet.scale(myScale.getX(), myScale.getY(), myScale.getZ());
			myDrawApplet.rotateX(myRot.getX());
			myDrawApplet.rotateY(myRot.getY());
			myDrawApplet.rotateY(myRot.getZ());
			myModels[currentDetailLevel].setParentApplet(myDrawApplet);
			
			
			myModels[currentDetailLevel].draw(myAlpha);
			myDrawApplet.popMatrix();
			
			//First time drawing invokes the showing.
			if(wholeObjectIsInited==false)wholeObjectIsInited=true;
		}
	}
	
	public void setAlpha(float alpha) {
		myAlpha = alpha;
	}
	
	/**
	 * actual Modelloading at detailchange 
	 * to be overwritten
	 */
	public void changeModelDetail(){}
	
	// methods to change or obtain the level of detail
	public void setDetailLevel(int p_detailLevel){
		currentDetailLevel = p_detailLevel;
	}
	public int getDetailLevel(){
		return currentDetailLevel;
	}
	
	/** 
	 *  method called when selected(rollover and click once), 
	 *  excludes doubleclick, 
	 *  overwritten by specialisations
	 */
	public void click(){
		// to be overwritten
	}
} // end ObjectsOfIterest



